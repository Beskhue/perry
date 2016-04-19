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
    private $handler;
    private $guzzleClient;

    public function __construct()
    {
        $this->handler = new CurlMultiHandler();
        $stack = HandlerStack::create($this->handler);
        $rateLimiter = new RateLimiter(new \Perry\RateLimitProvider\memoryRateLimitProvider());
        $stack->push($rateLimiter);
        
        $this->guzzleClient = new Client(['handler' => $stack]);
    }

    /**
     * Configure the options array.
     *
     * @param array $options Current options
     *
     * @return array Configured ption
     */
    private function configureOptions($currentOptions)
    {  
        $headers = [
            'Accept-language' => 'en',
            'User-Agent' => 'Perry/'.Perry::$version.' '.Setup::$userAgent,
        ];

        $config = [];

        if ('0.0.0.0:0' != Setup::$bindToIp) {
            $config['curl'] = [CURLOPT_INTERFACE => Setup::$bindToIp];
        }

        $options = [
            'headers' => $headers,
            'config' => $config,
        ];

        // merge in the ons from the options array
        $options = array_merge_recursive(Setup::$fetcherOptions, $currentOptions, $options);

        return $options;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param string $representation
     *
     * @return \GuzzleHttp\Promise\PromiseInterface that will resolve into a \Psr\Http\Message\ResponseInterface
     */
    private function responsePromise($method, $uri = null, $options = [])
    {
        $options = $this->configureOptions($options);
        
        if ($method == 'get' && $response = CacheManager::getInstance()->load($uri)) {
            $response->_fromCache = true;
            $promise = new Promise(
                function () use (&$promise, &$response) {
                    $promise->resolve($response);
                }
            );
        } else {
            $promise = $this->guzzleClient->requestAsync($method, $uri, $options);
        }

        return $promise;
    }
    
    /**
     * Asynchronously send a request to a CREST resource.
     *
     * @param string $method  The HTTP method to use.
     * @param string $uri     The URI the request if targeting.
     * @param array  $options The request options.
     *
     * @return \GuzzleHttp\Promise\PromiseInterface that will resolve into a \Perry\Response
     */
    public function requestAsync($method, $uri = null, $options = [])
    {
        $responsePromise = $this->responsePromise($method, $uri, $options);
        $promise = $responsePromise->then(
            function ($response) use (&$method, &$uri, &$options) {
                $representation = isset($options['representation']) ? $options['representation'] : null;
                
                $data = $response->getBody();
                $data = (String) $data;

                if ($response->hasHeader('Content-Type')) {
                    if (false !== ($retrep = Tool::parseContentTypeToRepresentation($response->getHeaderLine('Content-Type')))) {
                        $representation = $retrep;
                    }
                }

                if ($method == 'get' && !isset($response->_fromCache)) {
                    CacheManager::getInstance()->save($uri, $response);
                }

                return new Response($data, $representation);
            }
        );

        return $promise;
    }

    /**
     * Create a request pool that asynchronously sends requests to CREST resources.
     * 
     * @param array         $requestsSettings  Array of request settings.
     * @param null|callable $fulfilled         A callable of the form: function($response, $index).
     * @param null|callable $rejected          A callable of the form: function($reason, $index).
     *
     * @return \GuzzleHttp\Promise\PromisorInterface
     */
    public function requestsAsync($requestsSettings, $fulfilled = null, $rejected = null)
    {
        $guzzleRequests = function ($requestsSettings) {
            foreach ($requestsSettings as $requestSettings) {
                yield function () use (&$requestSettings) {
                    return $this->responsePromise(
                        $requestSettings['method'], 
                        $requestSettings['uri'], 
                        $requestSettings['options']
                    );
                };
            }
        };

        $pool = new Pool($this->guzzleClient, $guzzleRequests($requestsSettings), [
            'concurrency' => Setup::$concurrentRequests,
            'fulfilled' => function ($response, $index) use (&$requestsSettings, &$fulfilled) {
                    $method = $requestsSettings[$index]['method'];
                    $uri = $requestsSettings[$index]['uri'];

                    $data = $response->getBody();
                    $data = (String) $data;

                    if ($response->hasHeader('Content-Type')) {
                        if (false !== ($retrep = Tool::parseContentTypeToRepresentation($response->getHeaderLine('Content-Type')))) {
                            $representation = $retrep;
                        }
                    }

                    if ($method == 'get' && !isset($response->_fromCache)) {
                        CacheManager::getInstance()->save($uri, $response);
                    }
                    
                    $fulfilled(new Response($data, $representation), $index);
                },
            'rejected' => function ($reason, $index) use (&$rejected) {
                    $rejected($reason, $index);
                },
        ]);

        return $pool;
    }
    
    /** 
     * Synchronously wait for all outstanding connections to be handled.
     */
    public function execute()
    {
        $this->handler->execute();
    }
}
