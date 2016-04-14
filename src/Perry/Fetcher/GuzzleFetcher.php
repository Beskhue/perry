<?php
namespace Perry\Fetcher;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlMultiHandler;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Concat\Http\Middleware\RateLimiter;
use Perry\Cache\CacheManager;
use Perry\Perry;
use Perry\Response;
use Perry\Setup;
use Perry\Tool;

class GuzzleFetcher implements CanFetch
{
    private $guzzle;

    public function __construct()
    {
        $handler = new CurlMultiHandler();
        $stack = HandlerStack::create($handler);
        $rateLimiter = new RateLimiter(new \Perry\RateLimitProvider\memoryRateLimitProvider());
        $stack->push($rateLimiter);
        $this->guzzleClient = new Client(['handler' => $stack]);
    }

    /**
     * form the opts array
     *
     * @param string $representation
     * @return array
     */
    private function getOpts($representation)
    {
        $headers = [
            "Accept-language" => "en",
            'User-Agent' => 'Perry/' . Perry::$version . ' ' .Setup::$userAgent
        ];

        if(!is_null($representation)) 
        {
            $headers["Accept"] = "application/$representation+json";
        }

        $config =[];

        if("0.0.0.0:0" != Setup::$bindToIp) 
        {
            $config['curl'] = [CURLOPT_INTERFACE => Setup::$bindToIp];
        }

        $options = [
            "headers" => $headers,
            "config" => $config
        ];

        // merge in the ons from the options array
        $options = array_merge_recursive(Setup::$fetcherOptions, $options);

        return $options;
    }
    
    /**
     * @param string $url
     * @param string $representation
     * @return \GuzzleHttp\Promise\PromiseInterface that will resolve into a \Psr\Http\Message\ResponseInterface
     */
    private function _responsePromise($url, $representation)
    {
        if($response = CacheManager::getInstance()->load($url)) 
        {
            $response->_fromCache = true;
            $promise = new Promise(
                function() use (&$promise, &$response)
                {
                    $promise->resolve($response);
                }
            );
        }
        else 
        {
            $promise = $this->guzzleClient->requestAsync('GET', $url, $this->getOpts($representation));
        }
        
        return $promise;
    }
    
    /**
     * @param string $url
     * @param string $representation
     * @throws \Exception
     * @return \GuzzleHttp\Promise\PromiseInterface that will resolve into a \Perry\Response
     */
    public function doGetRequest($url, $representation)
    {
        $responsePromise = $this->_responsePromise($url, $representation);
        $promise = $responsePromise->then(
            function($response) use ($url, $representation)
            {
                $data = $response->getBody();
                $data = (String) $data;

                if($response->hasHeader("Content-Type")) 
                {
                    if(false !== ($retrep = Tool::parseContentTypeToRepresentation($response->getHeaderLine("Content-Type")))) 
                    {
                        $representation = $retrep;
                    }
                }

                if(!isset($response->_fromCache))
                {
                    CacheManager::getInstance()->save($url, $response);
                }

                return new Response($data, $representation);
            }
        );
        
        return $promise;
    }
    
    /**
     * @param array $requests Array of requests. A request is either a url
     * or an array of the form ['url' => ..., 'representation' => ...]. 
     * Representations are optional, thus ['url' => ...] is also allowed.
     * @param null|callable $fulfilled A callable of the form: function($response, $index)
     * @param null|callable $rejected A callable of the form: function($reason, $index)
     * @return \GuzzleHttp\Promise\PromisorInterface
     */
    public function doGetRequests($requests, $fulfilled, $rejected)
    {
        // Make request arrays/strings consistent
        foreach($requests AS &$request)
        {
            if(is_array($request))
            {
                if(!isset($request['representation']) || (isset($request['representation']) && !$request['representation']))
                {
                    $request['representation'] = null;
                } 
            }
            else 
            {
                $request = ['url' => $request, 'representation' => null];
            }
        }
        
        $guzzleRequests = function ($requests) use (&$fulfilled)
        {
            foreach($requests AS $request)
            {
                yield function() use (&$request)
                { 
                    return $this->_responsePromise($request['url'], $request['representation']); 
                };
            }
        };
        
        $pool = new Pool($this->guzzleClient, $guzzleRequests($requests), [
            'concurrency' => Setup::$concurrentRequests,
            'fulfilled' => 
                function ($response, $index) use (&$requests, &$fulfilled)
                {
                    $url = $requests[$index]['url'];
                
                    $data = $response->getBody();
                    $data = (String) $data;

                    if($response->hasHeader("Content-Type")) 
                    {
                        if(false !== ($retrep = Tool::parseContentTypeToRepresentation($response->getHeaderLine("Content-Type")))) 
                        {
                            $representation = $retrep;
                        }
                    }

                    if(!isset($response->_fromCache))
                    {
                        CacheManager::getInstance()->save($url, $response);
                    }

                    $fulfilled(new Response($data, $representation), $index);
                },
            'rejected' => 
                function ($reason, $index) use (&$rejected)
                {
                    $rejected($reason, $index);
                }
        ]);
        
        return $pool;
    }
}
