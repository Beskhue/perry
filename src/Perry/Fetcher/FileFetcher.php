<?php

namespace Perry\Fetcher;

use GuzzleHttp\Promise\Promise;
use Perry\Cache\CacheManager;
use Perry\Perry;
use Perry\Response;
use Perry\Setup;
use Perry\Tool;

/**
 * Class FileFetcher.
 *
 * @deprecated
 */
class FileFetcher implements CanFetch
{
    /**
     * Configure the options array.
     *
     * @param string $method HTTP method.
     * @param array  $options Current options
     *
     * @return array Configured ption
     */
    private function configureOptions($method, $currentOptions)
    {  
        $options = [
            'http' => [
                'method' => strtoupper($method),
                'header' => "Accept-language: en\r\n"
                            ."User-Agent: Perry/".Perry::$version." ".Setup::$userAgent."\r\n"
                            .(isset($currentOptions['headers']) ? $currentOptions['headers'].join('\r\n') . "\r\n" : '')
            ],
            'socket' => [
                'bindto' => Setup::$bindToIp,
            ]
        ];

        // merge in the ons from the options array
        $options = array_merge_recursive(Setup::$fetcherOptions, $currentOptions, $options);

        return $options;
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
    public function requestAsync($method, $uri = null, $options = null)
    {
        $promise = new Promise(
            function () use (&$promise, &$method, &$uri, &$options) {
                if ($method == "get" && $cachedData = CacheManager::getInstance()->load($uri)) {
                    $data = $cachedData['data'];
                    $headers = $cachedData['headers'];
                } else {
                    $context = stream_context_create($this->configureOptions($method, $options));
                    print_r($this->configureOptions($method, $options));
                    
                    if (false === ($data = @file_get_contents($uri, false, $context))) {
                        throw new \Exception(sprintf("An error occured with the file request: %s %s \n %s", $method, $uri, print_r($options,1)));
                    }

                    if (false === $headers = (@get_headers($uri, 1))) {
                        throw new \Exception(sprintf("Could not connect to API: %s %s \n %s", $method, $uri, print_r($options,1)));
                    }

                    if ($method == "get") {
                        CacheManager::getInstance()->save($uri, ['data' => $data, 'headers' => $headers]);
                    }
                }

                if (isset($headers['Content-Type'])) {
                    if (false !== ($retrep = Tool::parseContentTypeToRepresentation($headers['Content-Type']))) {
                        $representation = $retrep;
                    }
                }

                $response = new Response($data, $representation);
                $promise->resolve(new Response($data, $representation));
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
    public function requestsAsync($requests, $fulfilled, $rejected)
    {
        throw new \Exception('Not implemented.');
    }
    
    /** 
     * Synchronously wait for all outstanding connections to be handled.
     */
    public function execute()
    {
        throw new \Exception('Not implemented.');
    }
}
