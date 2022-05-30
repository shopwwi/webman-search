<?php
/**
 *-------------------------------------------------------------------------p*
 * es搜索
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

namespace Shopwwi\WebmanSearch\Adapter;

use Elastic\Elasticsearch\ClientBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Shopwwi\WebmanSearch\TraitFace\Collection;
use Shopwwi\WebmanSearch\TraitFace\ModelTrait;
use Shopwwi\WebmanSearch\TraitFace\WhereTrait;
use Illuminate\Support\Arr;

class ElasticSearch
{
    use WhereTrait;
    protected $model;
    protected $elasticsearch;
    protected $_index = 'goods';
    protected $_id = 'id';
    protected $_type = '_doc';
    protected $limit = 15;
    protected $sort = [];
    protected $ignores = [];
    protected $attributesToHighlight = [];
    protected $searchable = [];
    protected $facetsDistribution = [];
    protected $query = '';
    protected $flush_index = true;//立即刷新索引
    protected $fuzzy = true;//开启模糊搜索
    protected $auto_synonyms = true; //开启自动同义词搜索功能

    /**
     * 初始化
     * @param $options
     * @param $other
     * @return $this
     */
    public function make($options,$other)
    {
        if(isset($other['id'])){
            $this->_id = $other['id'];
        }
        if(isset($other['index'])){
            $this->_index = $other['index'];
        }
        if(isset($other['type'])){
            $this->_type = $other['type'];
        }
        // Check if connection is already loaded.
        $name = $options['default'] ?? 'default';
        $config = $options["connections"][$name];
        // Instantiate a new ClientBuilder
        $clientBuilder = ClientBuilder::create();
        $clientBuilder->setHosts($config["hosts"]);
        $clientBuilder = $this->configureLogging($clientBuilder, $config);
        if (!empty($config['handler'])) {
            $clientBuilder->setHandler($config['handler']);
        }
        // Build the client object
        $connection = $clientBuilder->build();
        $this->elasticsearch = $connection;
        return $this;
    }

    /**
     * 设置模型
     * @param $model
     * @return $this
     */
    public function model($model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * 返回实例
     * @return mixed
     */
    public function us()
    {
        return $this->elasticsearch;
    }

    /**
     * @param ClientBuilder $clientBuilder
     * @param array $config
     * @return ClientBuilder
     */
    private function configureLogging(ClientBuilder $clientBuilder, array $config)
    {
        if (Arr::get($config, 'logging.enabled')) {
            $logger = new Logger('name');
            $logger->pushHandler(new StreamHandler(Arr::get($config, 'logging.location'), Arr::get($config, 'logging.level', 'all')));
            $clientBuilder->setLogger($logger);
        }
        return $clientBuilder;
    }

    /**
     * @param $name
     * @return $this
     */
    public function index($name)
    {
        $this->_index = $name;
        return $this;
    }

    /**
     * 设置模糊搜索
     * @param $val
     * @return $this
     */
    public function fuzzy($val = true)
    {
        $this->fuzzy = $val;
        return $this;
    }

    /**
     * 查询关键词
     * @param $keywords
     * @return ElasticSearch
     */
    public function q($keywords)
    {
        $this->query = $keywords;
        return $this;
    }

    /**
     * 设置查询显示的属性
     * @param string[] $attributes
     */
    public function select($attributes = [])
    {
        $this->searchable = $attributes;
        return $this;
    }

    /**
     * 设置排序
     * @param $column
     * @param $rank
     * @return $this
     */
    public function orderBy($column, $rank = 'asc')
    {
        $this->sort[] = [$column => $rank];
        return $this;
    }

    /**
     * 新增更新文档
     * @param $data
     * @return void
     */
    public function create($data)
    {
        if (!is_array($data)) {
            throw new \Exception('你的索引参数不是一个数组');
        }
        if (isset($data[0]) && is_array($data[0])) {
            // 多维数组
            foreach ($data as $v) {
                $params = [
                    'index' => $this->_index,
                    'id' => $v[$this->_id],
                    'client' => ['ignore' => $this->ignores],
                    'body' => $v
                ];
                return $this->elasticsearch->index($params);
            }
        } else {
            // 一维数组
            $params = [
                'index' => $this->_index,
                'id' => $data[$this->_id],
                'client' => ['ignore' => $this->ignores],
                'body' => $data
            ];
            return $this->elasticsearch->index($params);
        }
    }

    /**
     * 更新文档
     * @param $data
     * @return void
     * @throws \Exception
     */
    public function update($data)
    {
        if (!is_array($data)) {
            throw new \Exception('你的索引参数不是一个数组');
        }
        if (isset($data[0]) && is_array($data[0])) {
            // 多维数组
            foreach ($data as $v) {
                $params = [
                    'index' => $this->_index,
                    'id' => $v[$this->_id],
                    'client' => ['ignore' => $this->ignores],
                    'body' => ['doc' => $v],
                ];
                return $this->elasticsearch->update($params);
            }
        } else {
            // 一维数组
            $params = [
                'index' => $this->_index,
                'id' => $data[$this->_id],
                'client' => ['ignore' => $this->ignores],
                'body' => ['doc' => $data],
            ];
            return $this->elasticsearch->update($params);
        }
    }

    /**
     * 删除指定文档
     * @param $ids
     * @return array
     * @throws \Exception
     */
    public function destroy($ids)
    {
        if (!$ids) {
            throw new \Exception('索引主键不能为空');
        }
        if(is_array($ids)){ //批量删除
            foreach ($ids as $id){
                $params = [
                    'index' => $this->_index,
                    'id'    => $id
                ];
                $this->elasticsearch->delete($params);
            }
        }else{ //单个删除
            $params = [
                'index' => $this->_index,
                'id'    => $ids
            ];
            return $this->elasticsearch->delete($params);
        }
    }


    /**
     * 清除索引内容
     */
    public function clear()
    {
        $params = [
            'index' => $this->_index,
            'client' => ['ignore' => $this->ignores]
        ];
        return $this->elasticsearch->indices()->delete($params);
    }

    /**
     * 设置查询数量
     * @param $limit
     * @return $this
     */
    public function limit(int $limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * 高亮查询
     * @param array $attributes
     * @return $this
     */
    public function highlight(array $attributes = [])
    {
        $this->attributesToHighlight = $attributes;
        return $this;
    }

    public function facets(array $attributes = ['*'])
    {
        $this->facetsDistribution = $attributes;
        return $this;
    }

    /**
     * 获取数据
     * @return void
     */
    public function get()
    {
        return $this->performSearch();
    }

    /**
     * 获取指定编号文档
     * @param $id
     * @return mixed
     */
    public function first($id)
    {
        $params = [
            'index' => $this->_index,
            'id'    => $id
        ];
        return  $this->getFirst($this->elasticsearch->get($params)->asArray());
    }

    /**
     * Perform the given search on the engine.
     *
     * @param int $page
     * @return mixed
     */
    public function paginate(int $page = 1)
    {
        $filters = [
            'offset' => ($page - 1) * $this->limit,
        ];
        return $this->performSearch($filters);
    }

    /**
     * 获取建议词
     * @return void
     */
    public function suggest($showNum = false)
    {
        $query = [
            'index' => $this->_index,
            'type'=> $this->_type,
            'suggest_field' => 'goods_name',
            'suggest_text' => $this->query,
            'suggest_mode' => 'always',
            'suggest_size' => 20,
//            'body' => [
//                'suggest' => [
//                    'song-suggest' => [
//                        'prefix' => $this->query,
//                        'completion' => [
//                          'field'=>'goods_mame.py'
//                        ]
//                    ]
//                ]
//            ]
        ];
        $result = $this->elasticsearch->search($query);
        return $result->asArray();
    }

    /**
     * Perform the given search on the engine.
     *
     * @return mixed
     */
    protected function performSearch(array $searchParams = [])
    {
        $query = [
            'index' => $this->_index,
            'type' => $this->_type,
        ];

        $query["body"] = $this->filters();
        $query["from"] = $searchParams['offset'] ?? 0;
        $query["size"] = $this->limit;

        if (count($this->ignores)) {
            $query["client"] = ['ignore' => $this->ignores];
        }


        //  $query["search_type"] = '';
        // $query["scroll"] = $scroll;

        $result = $this->elasticsearch->search($query);
        return $this->getAll($result->asArray());
    }
    /**
     * Retrieve only first record
     * @param array $result
     */
    protected function getFirst($result = [])
    {

        if (array_key_exists("_source", $result)) {
            $model = new ModelTrait();
            $model->setAttributes($result["_source"]);

            // match earlier version
            $model->_index = $result["_index"];
            $model->_type = $result["_type"];
            $model->_id = $result["_id"];
            $model->_score = $result["_score"] ?? [];
            $model->_highlight = isset($result["highlight"]) ? $result["highlight"] : [];
            $new = $model;
        } else {
            $new = NULL;
        }

        return $new;
    }
    /**
     * 数据整理
     * @param $result
     * @return \Illuminate\Support\Collection
     */
    protected function getAll($result = []){
        if (array_key_exists("hits", $result)) {
            $new = [];
            foreach ($result["hits"]["hits"] as $row) {
                $model = new ModelTrait();
                $model->setAttributes($row["_source"]);
                // match earlier version
                $model->_index = $row["_index"];
                $model->_type = isset($row["_type"]) ? $row["_type"] : 'doc';
                $model->_id = $row["_id"];
                $model->_score = $row["_score"];
                $model->_highlight = isset($row["highlight"]) ? $row["highlight"] : [];
                $new[] = $model;
            }
            $new = collect($new);
            $total = $result["hits"]["total"];
            $new->total = is_array($total) ? $total["value"] : $total;
            $new->page = request()->input('page',1);
            $new->max_score = $result["hits"]["max_score"];
            $new->took = $result["took"];
            $new->timed_out = $result["timed_out"];
            $new->scroll_id = isset($result["_scroll_id"]) ? $result["_scroll_id"] : NULL;
            $new->shards = (object)$result["_shards"];
            return $new;
        } else {
            return collect([]);
        }
    }

    /**
     * 数据整理
     * @return array|string
     */
    protected function filters()
    {

        $body = [];
        collect($this->wheres)->map(function ($item, $key) use (&$body) {
            switch ($item['type']) {
                case 'Basic':
                    if ($item['operator'] == "=") {
                        $body["query"]["bool"]["filter"][] = ["term" => [$item['column'] => $item['value']]];
                    }
                    if ($item['operator'] == ">") {
                        $body["query"]["bool"]["filter"][] = ["range" => [$item['column'] => ["gt" => $item['value']]]];
                    }
                    if ($item['operator'] == ">=") {
                        $body["query"]["bool"]["filter"][] = ["range" => [$item['column'] => ["gte" => $item['value']]]];
                    }
                    if ($item['operator'] == "<") {
                        $body["query"]["bool"]["filter"][] = ["range" => [$item['column'] => ["lt" => $item['value']]]];
                    }
                    if ($item['operator'] == "<=") {
                        $body["query"]["bool"]["filter"][] = ["range" => [$item['column'] => ["lte" => $item['value']]]];
                    }
                    if ($item['operator'] == "like") {
                        $body["query"]["bool"]["must"][] = ["match" => [$item['column'] => $item['value']]];
                    }
                    if ($item['operator'] == "exists") {
                        if (!$item['value']) {
                            $body["query"]["bool"]["must"][] = ["exists" => ["field" => $item['column']]];
                        } else {
                            $body["query"]["bool"]["must_not"][] = ["exists" => ["field" => $item['column']]];
                        }
                    }
                    break;
                case 'In':
                    $body["query"]["bool"]["filter"][] = ["terms" => [$item['column'] => $item['value']]];
                    break;
                case 'NotIn':
                    $body["query"]["bool"]["must_not"][] = ["terms" => [$item['column'] => $item['value']]];
                    break;
                case 'Raw':

                    break;
                case 'Between':
                    $body["query"]["bool"]["filter"][] = ["range" => [$item['column'] => ["gte" => $item['values'][0], "lte" => $item['values'][1]]]];
                    break;
                case 'NotBetween':
                    $body["query"]["bool"]["must_not"][] = ["range" => [$item['column'] => ["gte" => $item['values'][0], "lte" => $item['values'][1]]]];
                    break;

            }
        });
        if (count($this->searchable)) {
            $_source = array_key_exists("_source", $body) ? $body["_source"] : [];
            $body["_source"] = array_merge($_source, $this->searchable);
        }
        if(!empty($this->query)){
            $body["query"]["bool"]["must"][] = [
                "query_string" => ["query" => $this->query]
            ];
        }
        $body["query"] = isset($body["query"]) ? $body["query"]: [];
        if(count($body["query"]) == 0){
            unset($body["query"]);
        }
//        if(count($this->attributesToHighlight)){
//            $body["highlight"]['fields'] = $this->attributesToHighlight;
//        }
        if (count($this->sort)) {
            $sortFields = array_key_exists("sort", $body) ? $body["sort"] : [];
            $body["sort"] = array_unique(array_merge($sortFields, $this->sort), SORT_REGULAR);
        }
        return $body;
    }
}