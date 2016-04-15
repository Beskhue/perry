<?php

namespace Perry;

class Perry
{
    /**
     * @var string Version string
     */
    public static $version = '3.0.0-dev';

    /**
     * @param Psr\Http\Message\ResponseInterface $data 
     *
     * @throws Exception when the representation class does not exist.
     * 
     * @return \Perry\Representation\Base
     */
    private static function createRepresentation($data)
    {
        $classname = Tool::parseRepresentationToClass($data->representation);
        if (class_exists($classname)) {
            return new $classname($data);
        } else {
            throw new \Exception(sprintf("No class for representation %s", $data->representation));
        }
    }
    
    /**
     * @param string      $url
     * @param null|string $representation
     *
     * @return \GuzzleHttp\Promise\PromiseInterface that will resolve into a \Perry\Representation\Base
     */
    public static function fromUrl($url, $representation = null)
    {
        $dataPromise = Setup::getInstance()->fetcher->doGetRequest($url, $representation);
        $promise = $dataPromise->then(
            function ($data) {
                return Perry::createRepresentation($data);
            }
        );

        return $promise;
    }

    /**
     * @param array         $requests  Array of requests. An array is either a url
     *                                 or an array of the form ['url' => ..., 'representation' => ...]. 
     *                                 Representations are optional, thus ['url' => ...] is also allowed.
     * @param null|callable $fulfilled A callable of the form: function($response, $index)
     * @param null|callable $rejected  A callable of the form: function($reason, $index)
     *
     * @return \GuzzleHttp\Promise\PromisorInterface
     */
    public static function fromUrls($requests, $fulfilled = null, $rejected = null)
    {
        $wrapFulfilled = function ($data, $index) use (&$fulfilled, &$rejected) {
            try {
                if ($fufilled) {
                    $fulfilled(Perry::createRepresentation($data), $index);
                }
            } catch (\Exception $e) {
                if ($rejected) {
                    $rejected($e, $index);
                }
            }
        };

        return Setup::getInstance()->fetcher->doGetRequests($requests, $wrapFulfilled, $rejected);
    }

    /**
     * @param array $requests Array of requests.
     * @param int   $perBatch The number of requests to batch together.
     *
     * @return Generator A generator yielding request batches.
     */
    private static function batches($requests, $perBatch = null)
    {
        $numRequests = count($requests);

        if (!$perBatch) {
            $perBatch = Setup::$batchSize;
        }

        $numBatches = ceil($numRequests / $perBatch);

        for ($i = 0; $i < $numBatches; ++$i) {
            $idx = $i * $perBatch;
            yield ['requests' => array_slice($requests, $idx, $perBatch), 'startIndex' => $idx];
        }
    }

    /**
     * @param array         $requests  Array of requests. An array is either a url
     *                                 or an array of the form ['url' => ..., 'representation' => ...]. 
     *                                 Representations are optional, thus ['url' => ...] is also allowed.
     * @param null|callable $fulfilled A callable of the form: function($response, $index)
     * @param null|callable $rejected  A callable of the form: function($reason, $index)
     * @param int           $perBatch  The number of requests to batch together.
     *
     * @return Generator A generator yielding \GuzzleHttp\Promise\PromisorInterface objects.
     */
    public static function fromUrlsBatched($requests, $fulfilled = null, $rejected = null, $perBatch = null)
    {
        $batches = self::batches($requests, $perBatch);

        foreach ($batches as $batch) {
            $startIdx = $batch['startIndex'];

            $wrapFulfilled = function ($d, $index) use (&$fulfilled, &$batch) {
                if ($fulfilled) {
                    $fulfilled($d, $index + $batch['startIndex']);
                }
            };

            $wrapRejected = function ($d, $index) use (&$rejected, &$batch) {
                if ($rejected) {
                    $rejected($d, $index + $batch['startIndex']);
                }
            };

            yield self::fromUrls($batch['requests'], $wrapFulfilled, $wrapRejected);
        }
    }
    
    /** 
     * Synchronously wait for all outstanding connections to be handled.
     */
    public static function execute()
    {
        Setup::getInstance()->fetcher->execute();
    }
}
