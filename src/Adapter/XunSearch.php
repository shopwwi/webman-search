<?php
/**
 *-------------------------------------------------------------------------p*
 * 讯搜选定器
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

use Shopwwi\WebmanSearch\TraitFace\ModelTrait;
use Shopwwi\WebmanSearch\TraitFace\WhereTrait;

class XunSearch
{
    use WhereTrait;
    protected $xunsearch;
    protected $_index = 'goods';
    protected $limit = 15;
    protected $sorts = [];
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
        $file = config_path().'/plugin/shopwwi/search/ini/'.$this->_index.'.ini';
        $this->xunsearch = new \XS($file);
        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function index($name)
    {
        $this->index = $name;
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
     * @return XunSearch
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
        $this->sorts[$column] = $rank == 'asc';
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
                $this->xunsearch->getIndex()->add(new \XSDocument($v));
            }
        } else {
            // 一维数组
            $this->xunsearch->getIndex()->add(new \XSDocument($data));
        }
        //索引是否立即生效
        if ($this->flush_index) {
            $this->xunsearch->getIndex()->flushIndex();
        }
        return $this->xunsearch->getIndex();
    }

    /**
     * 更新文档
     * @param $data
     * @return void
     */
    public function update($data){
        if (!is_array($data)) {
            throw new \Exception('你的索引参数不是一个数组');
        }
        if (count($data) == count($data, 1)) {
            // 一维数组
            $this->xunsearch->getIndex()->update(new \XSDocument($data));
        }
        //索引是否立即生效
        if ($this->flush_index) {
            $this->xunsearch->getIndex()->flushIndex();
        }
        return $this->xunsearch->getIndex();
    }

    /**
     * 删除指定文档
     * @param $ids
     * @return array
     * @throws \Exception
     */
    public function destroy($ids){
        if (!$ids) {
            throw new \Exception('索引主键不能为空');
        }

        $this->xunsearch->getIndex()->del($ids);
        if ($this->flush_index) {
            $this->xunsearch->getIndex()->flushIndex();
        }
        return $this->xunsearch->getIndex();
    }

    /**
     * 清除索引内容
     */
    public function clear(){
        return  $this->xunsearch->getIndex()->clean();
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

    /**
     *
     * @param array $attributes
     * @return $this
     */
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
        $field = $this->xunsearch->getFieldId();
        $search = $this->xunsearch->getSearch();
        $search->addQueryString("{$field->name}:{$id}");
        $docs = $search->search();
        $new = [];
        foreach ($docs as $val){
            $model = new ModelTrait();
            $model->setAttributes($val->getFields());
            $new[] = $model;
        }
        $new = collect($new);
        return $new->first();
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
            'offset' => ($page - 1) * $this->limit,
        ];
        return $this->performSearch( $filters);
    }

    /**
     * 获取建议词
     * @return void
     */
    public function suggest($showNum = false)
    {
        $search = $this->xunsearch->getSearch();
        $list = $search->getExpandedQuery($this->query,$this->limit);
        $newList = collect([]);
        foreach ($list as $val){
            if($showNum){
                $num = $search->count($val);
            }else{
                $num = 0;
            }
           $newList->push(['keyword'=>$val,'num'=>$num]);
        }
        return $newList;
    }
    /**
     * Perform the given search on the engine.
     *
     * @return mixed
     */
    protected function performSearch(array $searchParams = [])
    {
        $count = $total = $search_cost = 0;
        $doc = $related = $corrected = $hot = [];
        $total_begin = microtime(true);
        
        $search = $this->xunsearch->getSearch();
        //热门词汇
    //    $hot = '$search->getHotQuery()';
        // fuzzy search 模糊搜索
        $search->setFuzzy($this->fuzzy);
        //设置排序
        $search->setMultiSort($this->sorts);

        $search->setQuery($this->query);
        //搜索词
        $this->filters($search);

        $search->setLimit($this->limit, $searchParams['offset'] ?? 0);

        $search_begin = microtime(true);
        $docs = $search->search();
        $search_cost = microtime(true) - $search_begin;

        // get other result
        $total = $search->getLastCount();    //最近一次搜索结果数
//        $count = $search->getDbTotal();      //数据库总数

        // try to corrected, if resul too few
//        if ($total < 1 || $total < ceil(0.001 * $total)) {
//            $corrected = $search->getCorrectedQuery();
//        }
        // get related query
//        $related = $search->getRelatedQuery();
        $total_cost = microtime(true) - $total_begin;
        $new = [];
        foreach ($docs as $val){
            $model = new ModelTrait();
            $model->setAttributes($val->getFields());
            $new[] = $model;
        }
        $new = collect($new);
//        $new->hot = $hot; //热门词汇
        $new->total = $total; //搜索结果统计
//        $new->count = $count; //数据库总数据
//        $new->corrected = $corrected; //搜索提示
//        $new->related = $related; //相关搜索
        $new->search_cost = $search_cost; //搜索所用时间
        $new->total_cost = $total_cost; //页面所用时间
        return $new;
    }

    /**
     * 数据整理
     * @return string
     */
    protected function filters($search)
    {

        collect($this->wheres)->map(function ($item,$key) use (&$search) {
            switch ($item['type']){
                case 'Basic':
                    if ($item['operator'] == "=") {
                        $search->addQueryString("{$item['column']}:{$item['value']}");
                    }
                    if ($item['operator'] == "!=") {
                        $search->addQueryString("{$item['column']}:{$item['value']}",3);
                    }
                    if ($item['operator'] == ">") {
                         $search->addRange($item['column'],$item['value'],null);
                    }
                    if ($item['operator'] == ">=") {
                        $search->addRange($item['column'],$item['value'],null);
                    }
                    if ($item['operator'] == "<") {
                        $search->addRange($item['column'],null,$item['value']);
                    }
                    if ($item['operator'] == "<=") {
                        $search->addRange($item['column'],null,$item['value']);
                    }
                    if ($item['operator'] == "like") {
                        $search->addQueryString("{$item['column']}:{$item['value']}");
                    }
                    break;
                case 'In':
                    $search->addQueryString("{$item['column']}:".collect($item['values'])->implode(','));
                    break;
                case 'NotIn':
                    $search->addQueryString("{$item['column']}:".collect($item['values'])->implode(','),2);
                    break;
                case 'Raw':
                    $search->addQueryString($item['sql']);
                    break;
                case 'Between':
                    $search->addRange($item['column'], $item['values'][0], $item['values'][1]);
                    break;

            }
        });

        return $search;
    }
    
}