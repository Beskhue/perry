<?php

namespace Perry;

/**
 * @method \Perry\Representation\Base get($uri, $options = [])
 * @method \Perry\Representation\Base head($uri, $options = [])
 * @method \Perry\Representation\Base put($uri, $options = [])
 * @method \Perry\Representation\Base post($uri, $options = [])
 * @method \Perry\Representation\Base patch($uri, $options = [])
 * @method \Perry\Representation\Base delete($uri, $options = [])
 * @method \GuzzleHttp\Promise\PromiseInterface getAsync($uri, $options = [])
 * @method \GuzzleHttp\Promise\PromiseInterface headAsync($uri, $options = [])
 * @method \GuzzleHttp\Promise\PromiseInterface putAsync($uri, $options = [])
 * @method \GuzzleHttp\Promise\PromiseInterface postAsync($uri, $options = [])
 * @method \GuzzleHttp\Promise\PromiseInterface patchAsync($uri, $options = [])
 * @method \GuzzleHttp\Promise\PromiseInterface deleteAsync($uri, $options = [])
 * @method getRequests($requestsSettings, $fulfilled = null, $rejected = null)
 * @method headRequests($requestsSettings, $fulfilled = null, $rejected = null)
 * @method putRequests($requestsSettings, $fulfilled = null, $rejected = null)
 * @method postRequests($requestsSettings, $fulfilled = null, $rejected = null)
 * @method patchRequests($requestsSettings, $fulfilled = null, $rejected = null)
 * @method deleteRequests($requestsSettings, $fulfilled = null, $rejected = null)
 * @method \GuzzleHttp\Promise\PromisorInterface getRequestsAsync($requestsSettings, $fulfilled = null, $rejected = null)
 * @method \GuzzleHttp\Promise\PromisorInterface headRequestsAsync($requestsSettings, $fulfilled = null, $rejected = null)
 * @method \GuzzleHttp\Promise\PromisorInterface putRequestsAsync($requestsSettings, $fulfilled = null, $rejected = null)
 * @method \GuzzleHttp\Promise\PromisorInterface postRequestsAsync($requestsSettings, $fulfilled = null, $rejected = null)
 * @method \GuzzleHttp\Promise\PromisorInterface patchRequestsAsync($requestsSettings, $fulfilled = null, $rejected = null)
 * @method \GuzzleHttp\Promise\PromisorInterface deleteRequestsAsync($requestsSettings, $fulfilled = null, $rejected = null)
 * @method \Generator getRequestsBatched($requestsSettings, $fulfilled = null, $rejected = null)
 * @method \Generator headRequestsBatched($requestsSettings, $fulfilled = null, $rejected = null)
 * @method \Generator putRequestsBatched($requestsSettings, $fulfilled = null, $rejected = null)
 * @method \Generator postRequestsBatched($requestsSettings, $fulfilled = null, $rejected = null)
 * @method \Generator patchRequestsBatched($requestsSettings, $fulfilled = null, $rejected = null)
 * @method \Generator deleteRequestsBatched($requestsSettings, $fulfilled = null, $rejected = null)
 * @method \Generator getRequestsBatchedAsync($requestsSettings, $fulfilled = null, $rejected = null)
 * @method \Generator headRequestsBatchedAsync($requestsSettings, $fulfilled = null, $rejected = null)
 * @method \Generator putRequestsBatchedAsync($requestsSettings, $fulfilled = null, $rejected = null)
 * @method \Generator postRequestsBatchedAsync($requestsSettings, $fulfilled = null, $rejected = null)
 * @method \Generator patchRequestsBatchedAsync($requestsSettings, $fulfilled = null, $rejected = null)
 * @method \Generator deleteRequestsBatchedAsync($requestsSettings, $fulfilled = null, $rejected = null)
 */
class Perry
{
    /**
     * @var string Version string
     */
    public static $version = '3.0.0-dev';

        
    /**
     * Magic method to catch methods invoked on this static object.
     *
     * @param string $method Name of the method that was called.
     * @param array  $args   Arguments that were passed to the method.
     *
     * @return \Perry\Representation\Base|\GuzzleHttp\Promise\PromiseInterface|\GuzzleHttp\Promise\PromisorInterface
     */
    public static function __callStatic($method, $args)
    {
        if (count($args) < 1) {
            throw new \InvalidArgumentException('Magic request methods require arguments');
        }
        
        if (substr($method, -13) === 'RequestsAsync') {
            return self::prepareRequestsSettingsAndCall(
                'requestsAsync',
                $args,
                substr($method, 0, -13)
            );
        } else if (substr($method, -8) === 'Requests') {
            return self::prepareRequestsSettingsAndCall(
                'requests',
                $args,
                substr($method, 0, -8)
            );
        } else if (substr($method, -20) === 'RequestsBatchedAsync') {
            return self::prepareRequestsSettingsAndCall(
                'requestsBatchedAsync',
                $args,
                substr($method, 0, -20)
            );
        } else if (substr($method, -15) === 'RequestsBatched') {
            return self::prepareRequestsSettingsAndCall(
                'requestsBatched',
                $args,
                substr($method, 0, -15)
            );
        } else if (substr($method, -5) === 'Async') {
            return self::prepareRequestSettingsAndCall(
                'requestAsync',
                $args,
                substr($method, 0, -5)
            );
        } else {
            return self::prepareRequestSettingsAndCall(
                'request',
                $args,
                $method
            );
        }
    }
    
    /**
     * Create a representation from the CREST response.
     *
     * @param string                             $uri Original request uri.
     * @param Psr\Http\Message\ResponseInterface $data CREST response.
     *
     * @throws Exception when the representation class does not exist.
     * 
     * @return \Perry\Representation\Base
     */
    private static function createRepresentation($uri, $response)
    {
        $classname = Tool::parseRepresentationToClass($response->representation);
        if (class_exists($classname)) {
            return new $classname($response);
        } else {
            throw new \Exception(sprintf("No class for representation %s", $response->representation));
        }
    }

    
    /**
     * Prepare the settings for a request and call a function with those settings.
     *
     * @param string $methodToCall  Name of the method to call.
     * @param array  $args          Arguments the request's settings have to prepared from.
     * @param string $requestMethod The (HTTP) request method to use (e.g. "get" or "post").
     * 
     * @return mixed The return value of the method that is to be called.
     */
    private static function prepareRequestSettingsAndCall($methodToCall, $args, $requestMethod = null)
    {
        $requestSettings = self::argsToRequestSettings($args, $requestMethod);
        
        return self::$methodToCall(
            $requestSettings['method'], 
            $requestSettings['uri'], 
            $requestSettings['options']
        );
    }
    
    /**
     * Prepare the settings for a numbero f requests and call a function with those settings.
     *
     * @param string $methodToCall  Name of the method to call.
     * @param array  $args          Arguments the requests' settings have to prepared from.
     * @param string $requestMethod The (HTTP) request method to use (e.g. "get" or "post").
     * 
     * @return mixed The return value of the method that is to be called.
     */
    private static function prepareRequestsSettingsAndCall($methodToCall, $args, $requestMethod = null)
    {
        $requestsSettingsCallbacks = self::argsToRequestsSettingsCallbacks($args, $requestMethod);
        
        return self::$methodToCall(
            $requestsSettingsCallbacks['requests'],
            $requestsSettingsCallbacks['fulfilled'],
            $requestsSettingsCallbacks['rejected'],
            $requestsSettingsCallbacks['perBatch']
        );
    }
    
    /**
     * Prepare the options of the request (e.g., a representation in the options 
     * will be turned into the associated Accept header).
     *
     * @param array $options Current options  of the request.
     *
     * @return array Potentially changed options.
     */
    private static function prepareOptions($options)
    {
        if (isset($options['representation'])) {
            if (!isset($options['headers'])) {
                $options['headers'] = [];
            }
                
            $options['headers']['Accept'] = "application/{$options['representation']}+json";
        }
        
        if (isset($options['body'])) {
            $options['body'] = json_encode($options['body']);
        }
        
        return $options;
    }
    
    /**
     * Process an array of arguments holding parameters for a request and
     * crate a request setting associative array.
     *
     * @param array @args Request parameters.
     *
     * @return array Request settings: method, uri and options.
     */
    private static function argsToRequestSettings($args, $method = null)
    {
        if (!$method) {
            if (count($args) < 2) {
                throw new \InvalidArgumentException('A request method and URI are required');
            }
            
            $method = array_shift($args);
        }
        
        if (count($args) < 1) {
            throw new \InvalidArgumentException('A URI is required');
        }
        
        $uri = $args[0];
        $options = isset($args[1]) ? $args[1] : [];
        $options = self::prepareOptions($options);
        
        return [
            'method' => $method,
            'uri' => $uri, 
            'options' => $options
        ];
    }
    
    /**
     * Process an array of arguments holding parameters for numerous requests 
     * and create an array of settings for requests.
     *
     * @param array @nArgs Array of request args
     *
     * @return array An array of request settings: a method, uri and options 
     *               per request.
     */
    private static function argsToRequestsSettings($nArgs, $method = null)
    {
        return array_map(function($args) use (&$method) {
            if (!$method) {
                if (!isset($args['method'])) {
                    throw new \InvalidArgumentException('A request method is required');
                }
                $method = $args['method'];
            }
            
            if (!isset($args['uri'])) {
                throw new \InvalidArgumentException('A request uri is required');
            }
            
            $uri = $args['uri'];
            $options = isset($args['options']) ? $args['options'] : [];
            $options = self::prepareOptions($options);
            
            return [
                'method' => $method,
                'uri' => $uri,
                'options' => $options
            ];
        }, $nArgs);
    }
    
    /**
     * Process an array of parameters (requests and callbacks) and create an 
     * associative array holding an array of settings for numerous requests
     * and callbacks.
     *
     * @param array $args    Array of an array of request args, a success callback
     *                       and a rejected callback.
     * @param string $method The HTTP verb of the quests.
     *
     * @return array         An array with an array of request settings, 
     *                       a callback to be called when a request is fulfilled, 
     *                       a callback to be called when a request is rejeceted,
     *                       a number indicating the amount of requests the should
     *                       by processed per batch.
     */
    private static function argsToRequestsSettingsCallbacks($args, $method = null)
    {
        if (count($args) < 1) {
            throw new \InvalidArgumentException('An array of request settings is required');
        }
        
        $requestArgs = $args[0];
        $fulfilledCallback = isset($args[1]) ? $args[1] : null;
        $rejectedCallback = isset($args[2]) ? $args[2] : null;
        $perBatch = isset($args[3]) ? $args[3] : 500;
        
        return [
            'requests' => self::argsToRequestsSettings($requestArgs, $method),
            'fulfilled' => $fulfilledCallback,
            'rejected' => $rejectedCallback,
            'perBatch' => $perBatch
        ];
    }
    
    /**
     * Send an asynchronous request.
     *
     * @param string $method The HTTP method to use.
     * @param string $uri The URI to send the request to.
     * @param array  $options Request options.
     *
     * @return \GuzzleHttp\Promise\PromiseInterface that will resolve into a \Perry\Representation\Base
     */
    public static function requestAsync($method, $uri = null, $options = null)
    {
        $dataPromise = Setup::getInstance()->fetcher->requestAsync($method, $uri, $options);
        $promise = $dataPromise->then(
            function ($data) use (&$uri) {
                return self::createRepresentation($uri, $data);
            }
        );

        return $promise;
    }
    
    /**
     * Synchronously send a request.
     *
     * @param string $method The HTTP method to use.
     * @param string $uri The URI to send the request to.
     * @param array  $options Request options.
     *
     * @return \Perry\Representation\Base
     */
    public static function request($method, $uri = null, $options = null)
    {
        return self::requestAsync($method, $uri, $options)->wait();
    }
    
    /**
     * Create a pool that can be started asynchronously. The requests in the pool 
     * are sent asynchronously.
     * 
     * @param array         $requestsSettings Array of request settings.
     * @param null|callable $fulfilled        A callable of the form: function($response, $index).
     * @param null|callable $rejected         A callable of the form: function($reason, $index).
     *
     * @return \GuzzleHttp\Promise\PromisorInterface
     */
    public static function requestsAsync($requestsSettings, $fulfilled = null, $rejected = null)
    {
        $wrapFulfilled = function ($data, $index) use (&$requestsSettings, &$fulfilled, &$rejected) {
            try {
                if ($fulfilled) {
                    if (is_array($requestsSettings[$index])) {
                        $uri = $requestsSettings[$index]['uri'];
                    } else {
                        $uri = $requestsSettings[$index];
                    }
                    
                    $fulfilled(self::createRepresentation($uri, $data), $index);
                }
            } catch (\Exception $e) {
                if ($rejected) {
                    $rejected($e, $index);
                }
            }
        };
        
        return Setup::getInstance()->fetcher->requestsAsync($requestsSettings, $wrapFulfilled, $rejected);
    }
    
    /**
     * Create and start a pool, and wait for it to finish. The requests in the pool 
     * are processed asynchronously. The callbacks are called for each finished
     * request.
     *
     * @param array         $requestsSettings Array of request settings.
     * @param null|callable $fulfilled        A callable of the form: function($response, $index).
     * @param null|callable $rejected         A callable of the form: function($reason, $index).
     */
    public static function requests($requestsSettings, $fulfilled = null, $rejected = null)
    {
        self::requestsAsync($requestsSettings, $fulfilled, $rejected)
            ->promise()
            ->wait();
    }
    
    /**
     * @param array $requestsSettings Array of request settings.
     * @param int   $perBatch         The number of requests to batch together.
     *
     * @return \Generator A generator yielding request setting batches.
     */
    private static function batches($requestsSettings, $perBatch = null)
    {
        $numRequests = count($requestsSettings);

        if (!$perBatch) {
            $perBatch = Setup::$batchSize;
        }

        $numBatches = ceil($numRequests / $perBatch);

        for ($i = 0; $i < $numBatches; ++$i) {
            $idx = $i * $perBatch;
            yield ['requestsSettings' => array_slice($requestsSettings, $idx, $perBatch), 'startIndex' => $idx];
        }
    }
    
    /**
     * Divide numerous requests into separate batches. Creates a generator that yields 
     * request pools for each batch. The callbacks are called for each finished request.
     * The indices in the callbacks are the indices of the requests as they are in 
     * $requestsSettings.
     * 
     * @param array         $requestsSettings Array of request  settings.
     * @param null|callable $fulfilled        A callable of the form: function($response, $index)
     * @param null|callable $rejected         A callable of the form: function($reason, $index)
     * @param int           $perBatch         The number of requests per batch.
     *
     * @return \Generator A generator yielding \GuzzleHttp\Promise\PromisorInterface objects.
     */
    public static function requestsBatchedAsync($requestsSettings, $fulfilled = null, $rejected = null, $perBatch = 500)
    {
        $batches = self::batches($requestsSettings, $perBatch);

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

            yield self::requestsAsync($batch['requestsSettings'], $wrapFulfilled, $wrapRejected);
        }
    }
    
    /**
     * Divide numerous requests into separate batches. Creates a generator that creates pools
     * for batches, starts them and waits for them to finish. Once a pool for a batch is finished,
     * the generator yields the index of the batch that was finished. The callbacks are called for 
     * each finished request. The indices in the callbacks are the indices of the requests as they 
     * are in $requestsSettings.
     * 
     * @param array         $requestsSettings Array of request  settings.
     * @param null|callable $fulfilled        A callable of the form: function($response, $index).
     * @param null|callable $rejected         A callable of the form: function($reason, $index).
     * @param int           $perBatch         The number of requests per batch.
     *
     * @return Generator A generator yielding \GuzzleHttp\Promise\PromisorInterface objects.
     */
    public static function requestsBatched($requestsSettings, $fulfilled = null, $rejected = null)
    {
        $i = 0;
        foreach($this->requestsBatchedAsync($requestsSettings, $fulfilled, $rejected) AS $promisor)
        {
            $promisor
                ->promise()
                ->wait();
            yield $i;
            $i++;
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
