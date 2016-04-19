<?php

namespace Perry\Fetcher;

interface CanFetch
{
    /**
     * Asynchronously send a request to a CREST resource.
     *
     * @param string $method  The HTTP method to use.
     * @param string $uri     The URI the request if targeting.
     * @param array  $options The request options.
     *
     * @return \GuzzleHttp\Promise\PromiseInterface that will resolve into a \Perry\Response
     */
    public function requestAsync($method, $uri = null, $options = null);

    /**
     * Create a request pool that asynchronously sends requests to CREST resources.
     * 
     * @param array         $requestsSettings  Array of request settings.
     * @param null|callable $fulfilled         A callable of the form: function($response, $index).
     * @param null|callable $rejected          A callable of the form: function($reason, $index).
     *
     * @return \GuzzleHttp\Promise\PromisorInterface
     */
    public function requestsAsync($requests, $fulfilled, $rejected);
    
    /** 
     * Synchronously wait for all outstanding connections to be handled.
     */
    public function execute();
    
}
