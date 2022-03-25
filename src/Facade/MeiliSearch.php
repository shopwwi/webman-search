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
declare (strict_types = 1);
namespace Shopwwi\WebmanMeilisearch\Facade;
/**
 * @see \Shopwwi\WebmanMeilisearch\MeiliSearch
 * @mixin \Shopwwi\WebmanMeilisearch\MeiliSearch
 * @method index($name) static 设置索引
 * @method q($keywords) static 查询关键词
 * @method where($column, $operator = null, $value = null, string $boolean = 'AND') static 搜索
 * @method orWhere($column, $operator = null, $value = null) static 搜索或者
 * @method whereIn($column, $values, string $boolean = 'AND', bool $not = false) static 存在当中
 * @method orWhereIn(string $column, $values) static 搜索或者存在当然
 * @method whereNotIn(string $column,array $values, string $boolean = 'AND') static 搜索不存在当中
 * @method orWhereNotIn(string $column, $values) static 搜索或者不存在当中
 * @method whereRaw($sql,  $boolean = 'AND') static 原生数据查询
 * @method orderBy($column,$rank) static 排序
 * @method create($data = []) static 新增文档
 * @method update($data = []) static 更新文档
 * @method destroy($id) static 删除 $id为数组时则是批量删除
 * @method select($attributes = ['*'])
 * @method get() static 获取数据
 * @method first($id) static 获取指定数据
 * @method clear() static 清除索引
 * @method highlight($attributes = []) static 设置高亮词
 * @method paginate(int $page = 1) static 获取分页
 */
class MeiliSearch
{
    protected static $_instance = null;


    public static function instance()
    {
        if (!static::$_instance) {
            static::$_instance = new \Shopwwi\WebmanMeilisearch\MeiliSearch();
        }
        return static::$_instance;
    }
    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return static::instance()->{$name}(... $arguments);
    }
}