<?php

namespace Perry\Fetcher;

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
     * form the opts array.
     *
     * @param string $representation
     *
     * @return array
     */
    private function getOpts($representation)
    {
        $opts = array(
            'http' => array(
                'method' => 'GET',
            ),
            'socket' => [
                'bindto' => Setup::$bindToIp,
            ],
        );

        if (is_null($representation)) {
            $header = "Accept-language: en\r\nUser-Agent: Perry/".Perry::$version.' '.Setup::$userAgent."\r\n";
        } else {
            $header = "Accept-language: en\r\nUser-Agent: Perry/".
                Perry::$version.' '.Setup::$userAgent."\r\nAccept: application/$representation+json\r\n";
        }

        $opts['http']['header'] = $header;

        $opts = array_merge_recursive(Setup::$fetcherOptions, $opts);

        return $opts;
    }

    /**
     * @param string $url
     * @param string $representation
     *
     * @throws \Exception
     *
     * @return \GuzzleHttp\Promise\Promise that will resolve into a \Perry\Response
     */
    public function doGetRequest($url, $representation)
    {
        $promise = new Promise(
            function () use (&$promise, &$url, &$representation) {
                if ($cachedData = CacheManager::getInstance()->load($url)) {
                    $data = $cachedData['data'];
                    $headers = $cachedData['headers'];
                } else {
                    $context = stream_context_create($this->getOpts($representation));

                    if (false === ($data = @file_get_contents($url, false, $context))) {
                        throw new \Exception('an error occured with the file request: '.$headers[0]);
                    }

                    if (false === $headers = (@get_headers($url, 1))) {
                        throw new \Exception('could not connect to api');
                    }

                    CacheManager::getInstance()->save($url, ['data' => $data, 'headers' => $headers]);
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
     * @param array         $requests  Array of requests. A request is either a url
     *                                 or an array of the form ['url' => ..., 'representation' => ...]. 
     *                                 Representations are optional, thus ['url' => ...] is also allowed.
     * @param null|callable $fulfilled A callable of the form: function($response, $index)
     * @param null|callable $rejected  A callable of the form: function($reason, $index)
     *
     * @return \GuzzleHttp\Promise\PromisorInterface
     */
    public function doGetRequests($requests, $fulfilled, $rejected)
    {
        throw new Exception('Not implemented.');
    }
    
    /** 
     * Synchronously wait for all outstanding connections to be handled.
     */
    public function execute()
    {
        throw new Exception('Not implemented.');
    }
}
