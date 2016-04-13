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
     * @return \GuzzleHttp\Promise\Promise that will resolve into a \Perry\Representation\Base
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
}
