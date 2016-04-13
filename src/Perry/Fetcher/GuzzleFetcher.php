<?php
namespace Perry\Fetcher;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Promise\Promise;
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
        $handler = new CurlHandler();
        $stack = HandlerStack::create($handler); // Wrap w/ middleware
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

        if (!is_null($representation)) {
            $headers["Accept"] = "application/$representation+json";
        }

        $config =[];

        if ("0.0.0.0:0" != Setup::$bindToIp) {
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
     * @throws \Exception
     * @return \GuzzleHttp\Promise\Promise that will resolve into a \Perry\Response
     */
    public function doGetRequest($url, $representation)
    {
        $responsePromise = $this->guzzleClient->requestAsync('GET', $url, $this->getOpts($representation));
        $promise = $responsePromise->then(
            function($response) use ($url, $representation)
            {
                if ($data = CacheManager::getInstance()->load($url)) {
                    return new Response($data['value'], $data['representation']);
                }
                
                $data = $response->getBody();
                $data = (String) $data;

                if ($response->hasHeader("Content-Type")) {
                    if (false !== ($retrep = Tool::parseContentTypeToRepresentation($response->getHeaderLine("Content-Type")))) {
                        $representation = $retrep;
                    }
                }

                CacheManager::getInstance()->save($url, ["representation" => $representation, "value" => $data]);

                return new Response($data, $representation);
            }
        );
        
        return $promise;
    }
}
