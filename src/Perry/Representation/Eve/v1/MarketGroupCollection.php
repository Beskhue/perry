<?php

namespace Perry\Representation\Eve\v1;

use Perry\Representation\Reference as Reference;
use Perry\Representation\Uri as Uri;
use Perry\Representation\Base as Base;

class MarketGroupCollection extends Base
{
    public $pageCount;

    public $items = [];

    public $totalCount;

    public $next;

    public $previous;

    // by Warringer\Types\Long
    public function setPageCount($pageCount)
    {
        $this->pageCount = $pageCount;
    }

    // by Warringer\Types\ArrayType
    public function setItems($items)
    {
        // by Warringer\Types\Base
        $converters = [];
        $converters['parentGroup'] = function ($value) { return new Reference($value); };
        $converters['href'] = function ($value) { return new Uri($value); };
        $converters['name'] = function ($value) { return $value; };
        $converters['types'] = function ($value) { return new Reference($value); };
        $converters['description'] = function ($value) { return $value; };

        $func = function ($value) use ($converters) {
            $return = new \ArrayObject($value, \ArrayObject::ARRAY_AS_PROPS);
            $return['parentGroup'] = isset($value->{'parentGroup'}) ? $converters['parentGroup']($value->{'parentGroup'}) : null;
            $return['href'] = isset($value->{'href'}) ? $converters['href']($value->{'href'}) : null;
            $return['name'] = isset($value->{'name'}) ? $converters['name']($value->{'name'}) : null;
            $return['types'] = isset($value->{'types'}) ? $converters['types']($value->{'types'}) : null;
            $return['description'] = isset($value->{'description'}) ? $converters['description']($value->{'description'}) : null;

            return $return;
        };

        foreach ($items as $key => $value) {
            $this->items[$key] = $func($value);
        }
    }

    // by Warringer\Types\Long
    public function setTotalCount($totalCount)
    {
        $this->totalCount = $totalCount;
    }

    // by Warringer\Types\Reference
    public function setNext($next)
    {
        $this->next = new Reference($next);
    }

    // by Warringer\Types\Reference
    public function setPrevious($previous)
    {
        $this->previous = new Reference($previous);
    }
}
