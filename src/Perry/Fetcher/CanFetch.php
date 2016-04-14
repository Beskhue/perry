<?php

namespace Perry\Fetcher;

interface CanFetch
{
    /**
     * @param string $url
     * @param string $representation
     *
     * @return \GuzzleHttp\Promise\PromiseInterface that will resolve into a \Perry\Response
     */
    public function doGetRequest($url, $representation);

    /**
     * @param array         $requests  Array of requests. A request is either a url
     *                                 or an array of the form ['url' => ..., 'representation' => ...]. 
     *                                 Representations are optional, thus ['url' => ...] is also allowed.
     * @param null|callable $fulfilled A callable of the form: function($response, $index)
     * @param null|callable $rejected  A callable of the form: function($reason, $index)
     *
     * @return \GuzzleHttp\Promise\PromisorInterface
     */
    public function doGetRequests($requests, $fulfilled, $rejected);
    
    /** 
     * Synchronously wait for all outstanding connections to be handled.
     */
    public function execute();
    
}
