<?php

namespace Shopwwi\WebmanSearch\TraitFace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Elasticsearch data model
 * Class Model
 * @package Basemkhirat\Elasticsearch
 */
class ModelTrait extends Model
{
    /**
     * @param array $attributes
     */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }
}