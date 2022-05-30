<?php

namespace Shopwwi\WebmanSearch\TraitFace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Elasticsearch data model
 * Class Model
 * @package Basemkhirat\Elasticsearch
 */
class Collection extends \Illuminate\Support\Collection
{
    /**
     * Get the collection of items as JSON.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Get the collection of items as Array.
     *
     * @return string
     */
    public function toArray()
    {
        return array_map(function($item){
            return $item->toArray();
        }, $this->items);
    }
}