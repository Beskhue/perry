<?php
namespace Perry\Fetcher;

interface CanFetch
{
    /**
     * @param string $url
     * @param string $representation
     * @return \GuzzleHttp\Promise\Promise that will resolve into a \Perry\Response
     */
    public function doGetRequest($url, $representation);
}
