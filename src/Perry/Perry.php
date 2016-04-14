<?php
namespace Perry;

use GuzzleHttp\Promise\Promise;

class Perry
{
    /**
     * @var string Version string
     */
    public static $version = "3.0.0-dev";

    /**
     * @param string $url
     * @param null|string $representation
     * @return \GuzzleHttp\Promise\PromiseInterface that will resolve into a \Perry\Representation\Base
     */
    public static function fromUrl($url, $representation = null)
    {
        $dataPromise = Setup::getInstance()->fetcher->doGetRequest($url, $representation);
        $promise = $dataPromise->then(
            function($data)
            {
                $classname = Tool::parseRepresentationToClass($data->representation);
                return new $classname($data);
            }
        );
                
        return $promise;
    }
    
    /**
     * @param array $requests Array of requests. An array is either a url
     * or an array of the form ['url' => ..., 'representation' => ...]. 
     * Representations are optional, thus ['url' => ...] is also allowed.
     * @param null|callable $fulfilled A callable of the form: function($response, $index)
     * @param null|callable $rejected A callable of the form: function($reason, $index)
     * @return \GuzzleHttp\Promise\PromisorInterface
     */
    public static function fromUrls($requests, $fulfilled = null, $rejected = null)
    {
        $wrapFulfilled = function($data, $index) use (&$fulfilled)
        {
            $classname = Tool::parseRepresentationToClass($data->representation);
            $fulfilled(new $classname($data), $index);
        };
        
        return Setup::getInstance()->fetcher->doGetRequests($requests, $wrapFulfilled, $rejected);
    }
}
