<?php

namespace Perry\Representation;

use Perry\Response;
use Perry\Setup;

class Base
{
    protected $genericMembers = array();

    /**
     * @param null|array|object|string $inputData
     *
     * @throws \Exception
     */
    public function __construct($inputData)
    {
        $inputData = $this->cleanInputData($inputData);

        foreach ($inputData as $key => $value) {
            $method = 'set'.ucfirst($key);
            // if there is a setter method for this call the setter
            if (method_exists($this, $method)) {
                $this->{$method}($value);
            } else {
                $this->genericMembers[$key] = $value;
            }
        }
    }

    /**
     * Clean the input data.
     *
     * @param array|object|null|string $inputData
     *
     * @throws \Exception
     * @returns array
     */
    private function cleanInputData($inputData)
    {
        switch (true) {
            case $inputData instanceof Response:
                $inputData = json_decode((string) $inputData);
                break;
            case is_null($inputData):
                throw new \Exception('Got NULL in Base Construtor.');
            case is_string($inputData):
                $inputData = json_decode($inputData);
                break;
            case is_object($inputData):
                $inputData = get_object_vars($inputData);
                break;
            default:
        }

        if (!is_array($inputData) && !is_object($inputData)) {
            throw new \Exception("inputData is not an array, and therefore can't be traversed");
        }

        return $inputData;
    }

    /**
     * @param string $key
     *
     * @return \Perry\Representation\Base|string|int|float|null
     */
    public function __get($key)
    {
        if (isset($this->genericMembers[$key])) {
            return $this->genericMembers[$key];
        }

        return;
    }

    /**
     * @param string $method
     * @param array  $args
     *
     * @return \Perry\Representation\Base
     *
     * @throws \Exception
     */
    public function __call($method, $args)
    {
        if (isset($this->{$method}) && $this->{$method} instanceof Interfaces\CanRefer) {
            /**
             * @var Interfaces\CanRefer
             */
            $reference = $this->{$method};

            return $reference->call($args);
        } else {
            throw new \Exception("$method does not exist with this object");
        }
    }

    /**
     * Magic isset method, to ensure keys are found.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->genericMembers[$key]);
    }

    /**
     * @param string $url
     * @param string $representation
     *
     * @throws \Exception
     *
     * @return Response
     */
    protected static function doGetRequest($url, $representation)
    {
        // use configured fetcher
        return Setup::getInstance()->fetcher->doGetRequest($url, $representation);
    }
}
