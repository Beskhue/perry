<?php
namespace Perry\Representation\Eve\v1;

use \Perry\Representation\Reference as Reference;
use \Perry\Representation\Uri as Uri;
use \Perry\Representation\Base as Base;

class CorporationRolesCollection extends Base
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
        $converters['name'] = function ($value) { return $value; };
        $converters['href'] = function ($value) { return new Uri($value); };
        $converters['id'] = function ($value) { return $value; };
        $converters['isDustRole'] = function ($value) { return $value; };
        $converters['isEveRole'] = function ($value) { return $value; };

        $func = function ($value) use($converters) {
            $return = new \ArrayObject($value, \ArrayObject::ARRAY_AS_PROPS);
            $return['name'] = isset($value->{'name'}) ? $converters['name']($value->{'name'}) : null;
            $return['href'] = isset($value->{'href'}) ? $converters['href']($value->{'href'}) : null;
            $return['id'] = isset($value->{'id'}) ? $converters['id']($value->{'id'}) : null;
            $return['isDustRole'] = isset($value->{'isDustRole'}) ? $converters['isDustRole']($value->{'isDustRole'}) : null;
            $return['isEveRole'] = isset($value->{'isEveRole'}) ? $converters['isEveRole']($value->{'isEveRole'}) : null;
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
