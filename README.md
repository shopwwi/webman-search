[!['Build Status'](https://travis-ci.org/shopwwi/webman-search.svg?branch=main)](https://github.com/shopwwi/webman-scout) [!['Latest Stable Version'](https://poser.pugx.org/shopwwi/webman-search/v/stable.svg)](https://packagist.org/packages/shopwwi/webman-search) [!['Total Downloads'](https://poser.pugx.org/shopwwi/webman-search/d/total.svg)](https://packagist.org/packages/shopwwi/webman-search) [!['License'](https://poser.pugx.org/shopwwi/webman-search/license.svg)](https://packagist.org/packages/shopwwi/webman-search)

# 安装

```
composer require shopwwi/webman-search
```

- 如果觉得方便了你 不要吝啬你的小星星哦 tycoonSong<8988354@qq.com>
## 使用方法

### 搜索选定器
- xunsearch 使用讯搜搜索选定器
```
composer require hightman/xunsearch
```
- meilisearch 使用meilisearch选定器
```
composer require meilisearch/meilisearch-php
```
- elasticsearch 使用elasticsearch选定器（需8.0以上版本）
```
composer require elasticsearch/elasticsearch
```
### 搜索选定器服务端安装
- meilisearch 安装方法查看 https://docs.meilisearch.com/learn/getting_started/quick_start.html

- xunsearch 安装方法查看 http://www.xunsearch.com/doc/php/guide/start.installation

- elasticsearch  安装方法查看 https://www.elastic.co/

### 搜索配置

- 配置config/plugin/shopwwi/search/app.php 内配置 默认不需要配置

- 生成密钥(如果生成了 启动meilisearch服务端的时候记得保持一致，其它搜索器不需要)
```
php webman shopwwi:search
```
- 选定器调用（默认调用config设置的默认）
```php
    use \Shopwwi\WebmanSearch\Facade\Search;
    // id为主键字段  index为索引
    $search = Search::use('meilisearch',['id'=>'id','index'=>'index']);
 ```
- 执行命令方法
```php

php webman search:create:index  // es搜索必须先执行此命令创建索引
php webman search:update:index  // 更新索引配置
php webman search:drop:index  //删除索引

 ```
- 写入文档

```php
      $data = [
        ['id' => 1, 'title' => '我从来都不曾承认我其实是个大帅哥' ,'create_at' => 2022-03-24 08:08:08,'type' => 'A'],
        ['id' => 2, 'title' => '时间万物除我之外' ,'create_at' => 2022-03-24 09:08:08,'type' => 'B'],
        ['id' => 3, 'title' => '你看见我的小熊了吗？' ,'create_at' => 2022-03-24 10:08:08,'type' => 'B'],
        ['id' => 4, 'title' => '这是一个神奇的世界，因为你永远不会知道我在哪' ,'create_at' => 2022-03-24 10:08:08,'type' => 'C']
      ]
    $search->create($data); //批量写入 支持单条 写入一维数组即可
 ```

- 更新文档

```php

      $data = [
        ['id' => 3, 'title' => '你看见我的小熊了吗？哈哈哈']
      ];
    $search->update($data);  // 批量修改 主键必须存在 单条修改写入一维数组
 ```

- 删除文档

```php

    $search->destroy(3);  //则会删除id为3的数据
    $search->destroy([2,3]); //批量删除 id为2和3的数据
 ```

- 删除索引

```php
    $search->clear();
 ```

- 数据查询

```php
    use \Shopwwi\WebmanSearch\Facade\Search;
    // 基础查询关键词
    $result = $search->q('我')->get();
    // 全部查询 默认只显示20条
    $result = $search->get();
    //指定字段(默认指定所有)
    $result = $search->select(['id','title'])->get();
    //限定查询数量 默认为20
    $result = $search->limit(100)->q('我')->get();
    //带条件搜索（一定要设定可检索字段 不然无效，一般在初始化新增之后）
    // where($column, $operator = null, $value = null, string $boolean = 'AND') static 搜索
    // orWhere($column, $operator = null, $value = null) static 搜索或者
    
    Search::updateFilterableAttributes(['id','title','type','create_at']);
    $result = $search->where('type','B')->limit(100)->q('我')->get();
    $result = $search->where('type','!=','B')->limit(100)->q('我')->get();
    $result = $search->orWhere('type','B')->limit(100)->q('我')->get();
    // whereIn($column, $values, string $boolean = 'AND', bool $not = false) static 存在当中
    // orWhereIn(string $column, $values) static 搜索或者存在当然
    // whereNotIn(string $column,array $values, string $boolean = 'AND') static 搜索不存在当中
    // orWhereNotIn(string $column, $values) static 搜索或者不存在当中
    $result = $search->whereIn('type',['A','B'])->limit(100)->q('我')->get();
    $result = $search->whereNotIn('type',['A','B'])->limit(100)->q('我')->get();
    $result = $search->orWhereIn('type',['A','B'])->limit(100)->q('我')->get();
    $result = $search->orWhereNotIn('type',['A','B'])->limit(100)->q('我')->get();
    // whereRaw($sql,  $boolean = 'AND') static 原生数据查询
    $result = $search->where('type','B')->whereRaw('(id = 1 OR id = 2)')->limit(100)->q('我')->get();
    //whereBetween($column,array $values, string $boolean = 'AND')
    $result = $search->whereBetween('id',[1,5])->limit(100)->q('我')->get();
    //如果您的文档包含_geo数据，您可以使用_geoRadius内置过滤规则根据其地理位置过滤结果
    $result = $search->where('type','B')->whereRaw('_geoRadius(45.4628328, 9.1076931, 2000)')->limit(100)->q('我')->get();
    
    // 分页
    $result = $search->where('type','B')->limit(20)->q('我')->paginate(\request()->input('page',1));
    
    //关键词高亮
    $result = $search->where('type','B')->limit(20)->highlight(['title'])->q('我')->paginate(\request()->input('page',1));

 ```
- elasticSearch新升级whereRaw特定写法 支持query下的全部写法
```php
    $search->whereRaw(['bool'=>['filter'=>[['term'=>['color'=>'red']],['term'=>['color'=>'blue']]]]]);
    $search->whereRaw(['bool'=>['must'=>[['term'=>['color'=>'red']],['term'=>['color'=>'blue']]]]]);
    // 如需使用aggs
    $result = $search->aggs(['avg_price'=>['avg'=>['field'=>'price']]])->q('华为')->get();
     // 获取原文返回内容
    $result->raw
```

- 获取建议词（如搜索s则会出现与s相关的汉字数组 ,meilisearch不支持，es请使用原sdk方法查询）
```php
    
    $result = $search->limit(20)->q('s')->suggest();// 不带统计数，数量都为0
    $result = $search->limit(20)->q('s')->suggest(true); // 带统计数
 ```
- 获取指定文档

```php
    $result = $search->first(2);
 ```

- 字段排序

```php
    // 字段排序（一定要设定可排序字段 不然无效，一般在初始化新增之后） 
    //1.使用orderBy方法
    $result = $search->where('type','B')->orderBy('create_at','desc')->orderBy('id')->limit(100)->q('我')->get();
    //2.全局设定
    $result = $search->where('type','B')->limit(100)->q('我')->get();
 ```

- 获取原SDK方法（各方法请查看各选定器包说明文档）
```php
   //返回所选定器本身SDK方法
    $search = Search::use('meilisearch',['id'=>'id','index'=>'index'])->us();

    //如meilisearch调用任务方法
    $search->getTasks(); // 查询所有任务
```