<?php
/**
 *-------------------------------------------------------------------------p*
 *
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

use MeiliSearch\Client;
use Shopwwi\WebmanSearch\Support\Collection;
use Shopwwi\WebmanSearch\Support\Model;
use Shopwwi\WebmanSearch\TraitFace\SettingsTrait;
use Shopwwi\WebmanSearch\TraitFace\WhereTrait;

class MeiliSearch
{
    use WhereTrait;
    use SettingsTrait;
    /**
     * The Meilisearch client.
     *
     */
    protected $meilisearch;
    protected $_index = 'goods';
    protected $_id = 'id';
    protected $_type = '_doc';
    protected $limit = 20;
    protected $sorts = [];
    protected $attributesToHighlight = [];
    protected $searchable = [];
    protected $facetsDistribution = [];
    protected $query = '';

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
        $this->meilisearch = new Client($options['api'],$options['key']);
        return $this;
    }

    /**
     * 获取自身
     * @return mixed
     */
    public function us()
    {
        return $this->meilisearch;
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
     * 查询关键词
     * @param $keywords
     * @return MeiliSearch
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
        $this->sorts[] = sprintf("%s:%s", $column, $rank);
        return $this;
    }

    /**
     * 新增更新文档
     * @param $data
     * @return void
     */
    public function create($data)
    {
        $index = $this->meilisearch->index($this->_index);
        if (!empty($data)) {
            $index->addDocuments($data);
        }
    }

    /**
     * 更新文档
     * @param $data
     * @return void
     */
    public function update($data){
        $index = $this->meilisearch->index($this->_index);
        if (!empty($data)) {
            $index->updateDocuments($data);
        }
    }

    /**
     * 删除指定文档
     * @param $id
     * @return array
     */
    public function destroy($id){
        if(is_array($id)){ //批量删除
            return $this->meilisearch->index($this->_index)->deleteDocuments($id);
        }else{ //单个删除
            return $this->meilisearch->index($this->_index)->deleteDocument($id);
        }
    }

    /**
     * 清除索引内容
     */
    public function clear(){
        return  $this->meilisearch->index($this->_index)->delete();
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
        $filters = [
            'filter' => $this->filters(),
            'limit' => (int) $this->limit
        ];
        if(!empty($this->sorts)){
            $filters['sort'] = $this->sorts;
        }
        if(!empty($this->attributesToHighlight)){
            $filters['attributesToHighlight'] = $this->attributesToHighlight;
        }
        if(!empty($this->searchable)){
            $filters['attributesToRetrieve'] = $this->searchable;
        }
        if(!empty($this->facetsDistribution)){
            $filters['facetsDistribution'] = $this->facetsDistribution;
        }
        return $this->performSearch( array_filter($filters));
    }

    /**
     * 获取指定编号文档
     * @param $id
     * @return mixed
     */
    public function first($id)
    {
        $doc = $this->meilisearch->index($this->_index)->getDocument($id);
        $model = new Model();
        $model->setAttributes($doc);
        return $model;
    }

    /**
     * Perform the given search on the engine.
     *
     * @param $search
     * @param int $perPage
     * @param int $page
     * @return mixed
     */
    public function paginate(int $page = 1)
    {
        $filters = [
            'filter' => $this->filters(),
            'limit' => (int) $this->limit,
            'offset' => ($page - 1) * $this->limit,
        ];
        if(!empty($this->sorts)){
            $filters['sort'] = $this->sorts;
        }
        if(!empty($this->attributesToHighlight)){
            $filters['attributesToHighlight'] = $this->attributesToHighlight;
        }
        if(!empty($this->searchable)){
            $filters['attributesToRetrieve'] = $this->searchable;
        }
        if(!empty($this->facetsDistribution)){
            $filters['facetsDistribution'] = $this->facetsDistribution;
        }
        return $this->performSearch( array_filter($filters));
    }
    /**
     * Perform the given search on the engine.
     *
     * @return mixed
     */
    protected function performSearch(array $searchParams = [])
    {
        $meilisearch = $this->meilisearch->index($this->_index);
        $result = $meilisearch->rawSearch($this->query, $searchParams);
        $collect = new Collection([]);
        $collect->items = $result['hits'];
        $collect->total = $result['nbHits'];
        $collect->exhaustiveNbHits = $result['exhaustiveNbHits'];
        $collect->query = $result['query'];
        $collect->limit = $result['limit'];
        $collect->offset = $result['offset'];
        $collect->processingTimeMs = $result['processingTimeMs'];
        return $collect;
    }

    /**
     * 数据整理
     * @return string
     */
    protected function filters()
    {
        $filter = '';
        collect($this->wheres)->map(function ($item,$key) use (&$filter) {
            switch ($item['type']){
                case 'Basic':
                    if($key != 0) $filter .= "  {$item['boolean']}  ";
                    $filter .= sprintf("%s {$item['operator']} %s", $item['column'], $item['value']);
                    break;
                case 'In':
                    if($key != 0) $filter .= "  {$item['boolean']}  ";
                    $inString =  collect($item['values'])->map(function ($value, $key) use ($item) {
                        return sprintf("%s = %s", $item['column'], $value);
                    })->values()->implode(' OR ');
                    $filter .= "({$inString})";
                    break;
                case 'NotIn':
                    if($key != 0) $filter .= "  {$item['boolean']}  ";
                    $inString =  collect($item['values'])->map(function ($value, $key) use ($item) {
                        return sprintf("%s != %s", $item['column'], $value);
                    })->values()->implode(' OR ');
                    $filter .= "({$inString})";
                    break;
                case 'Raw':
                    if($key != 0) $filter .= "  {$item['boolean']}  ";
                    $filter .= "{$item['sql']}";
                    break;
                case 'Between':
                    if($key != 0) $filter .= "  {$item['boolean']}  ";
                    $filter .= " {$item['column']} {$item['values'][0]} TO {$item['values'][1]} ";
                    break;

            }
        });
        return $filter;
    }
    public function __call($method, $parameters)
    {
        return $this->meilisearch->$method(...$parameters);
    }
}