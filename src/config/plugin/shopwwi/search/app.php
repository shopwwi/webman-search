<?php
/**
 *-------------------------------------------------------------------------p*
 * 配置文件
 *-------------------------------------------------------------------------h*
 * @copyright  Copyright (c) 2015-2022 Shopwwi Inc. (http://www.shopwwi.com)
 *-------------------------------------------------------------------------c*
 * @license    http://www.shopwwi.com        s h o p w w i . c o m
 *-------------------------------------------------------------------------e*
 * @link       http://www.shopwwi.com by 象讯科技 phcent.com
 *-------------------------------------------------------------------------n*
 * @since      shopwwi象讯·PHP商城系统Pro
 *-------------------------------------------------------------------------t*
 */

return [
    'enable' => true,
    'default' => 'xunsearch',
    'holder' => [
        'meilisearch'=>[
            'driver' => \Shopwwi\WebmanSearch\Adapter\MeiliSearch::class,
            'key' => '',
            'api' => 'http://127.0.0.1:7700',
            'indices' => [
                'goods' => [
                    'searchable' => ['*'], // 搜索字段
                    'displayed' => ['*'], //显示字段
                    'rank' => [],
                    'sortable' => [], // 可排序字段
                    'filterable' => [] //可检索字段
                ]
            ]
        ],
        'elasticsearch'=>[
            'driver' => \Shopwwi\WebmanSearch\Adapter\ElasticSearch::class,
            'default' => 'default',
            'connections' => [
                'default' => [
                    'hosts' => [
                        'http://127.0.0.1:9200'
                    ],
                    'logging' => [
                        'enabled' => false,
                        'level' => 'all',
                        'location' =>  runtime_path().'/logs/elasticsearch.log'
                    ],
                ]
            ],
            /*
            |--------------------------------------------------------------------------
            | Elasticsearch Indices
            |--------------------------------------------------------------------------
            |
            | Here you can define your indices, with separate settings and mappings.
            | indices on elasticsearch server.
            |
            | 'my_index' is just for test. Replace it with a real index name.
            |
            */
            'indices' => [
                'goods' => [
                    'aliases' => [
                        'goodsCommon'
                    ],
                    'settings' => [
                        'number_of_shards' => 1,
                        'number_of_replicas' => 0,
                        "index.mapping.ignore_malformed" => false,
                        "analysis" => [
                            "analyzer" => [
                                'trigram' => [
                                    "type" => "custom",
                                    "tokenizer" => "standard",
                                    "filter" => ["lowercase","shingle"]
                                ],
                                'reverse' => [
                                    "type" => "custom",
                                    "tokenizer" => "standard",
                                    "filter" => ["lowercase","reverse"]
                                ],
                                "pinyin_analyzer" =>[
                                    "type" => "custom",
                                    "tokenizer" => "my_pinyin",
                                    "filter" => ["lowercase","shingle"]
                                ]
                            ],
                            'filter' => [
                                'shingle' => [
                                    'type' => 'shingle',
                                    'min_shingle_size' => 2,
                                    'max_shingle_size' => 3
                                ]
                            ],
                            'tokenizer' => [
                                'my_pinyin' => [
                                    'type' => 'pinyin',
                                    'keep_first_letter' => true,
                                    'keep_separate_first_letter' => true,
                                    'keep_full_pinyin' => true,
                                    'keep_original' => true,
                                    'limit_first_letter_length' => 20,
                                    'lowercase' => true,
                                ]
                            ]
                        ]
                    ],
                    'mappings' => [
                        '_doc' => [
                            'properties' => [
                                'suggest' => [
                                    'type' => 'completion'
                                ],
                                'title' => [
                                    'type' => 'text',
                                    'analyzer' => 'ik_max_word',
                                    'fields' => [
                                        'py' => [
                                            'type'=>'completion',
                                            'analyzer' => 'pinyin_analyzer'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]

            ]
        ],
        'xunsearch'=>[
            'driver' => \Shopwwi\WebmanSearch\Adapter\XunSearch::class,
            'indices' => [
                'goods' => []
            ]
        ]
    ]
];