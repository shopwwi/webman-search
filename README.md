# 安装

```
composer require shopwwi/webman-meilisearch
```

## 使用方法

- 服务器先安装meilisearch 具体方法查看https://docs.meilisearch.com/learn/getting_started/quick_start.html


- 配置config/plugin/shopwwi/meilisearch/app.php 内配置 默认不需要配置

- 生成密钥(如果生成了 启动meilisearch服务端的时候记得保持一致)
```
php webman shopwwi:meilisearch
```

- 写入文档

```php
    use \Shopwwi\WebmanMeilisearch\Facade\MeiliSearch;
      $data = [
        ['id' => 1, 'title' => '我从来都不曾承认我其实是个大帅哥' ,'create_at' => 2022-03-24 08:08:08,'type' => 'A'],
        ['id' => 2, 'title' => '时间万物除我之外' ,'create_at' => 2022-03-24 09:08:08,'type' => 'B'],
        ['id' => 3, 'title' => '你看见我的小熊了吗？' ,'create_at' => 2022-03-24 10:08:08,'type' => 'B'],
        ['id' => 4, 'title' => '这是一个神奇的世界，因为你永远不会知道我在哪' ,'create_at' => 2022-03-24 10:08:08,'type' => 'C']
      ]
    MeiliSearch::create($data);
    // 指定索引 如果你的项目只有一个索引是不需要指定的 但是如果有多个 则必须哦 不然都进入默认的了
    MeiliSearch::index('article')->create($data);
 ```

- 更新文档

```php
    use \Shopwwi\WebmanMeilisearch\Facade\MeiliSearch;
      $data = [
        ['id' => 3, 'title' => '你看见我的小熊了吗？哈哈哈']
      ];
    MeiliSearch::update($data); 
    // 用create也是可以的 create和update的区别？
    // update是更新指定字段 则id = 3的数据 title会被更新 其它字段不会丢失
    // create 则是更新唯一ID一整行数据 则现有数据会替换之前的数据
    MeiliSearch::create($data);
 ```

- 删除文档

```php
    use \Shopwwi\WebmanMeilisearch\Facade\MeiliSearch;
    MeiliSearch::destroy(3);  //则会删除id为3的数据
    MeiliSearch::destroy([2,3]); //批量删除 id为2和3的数据
    //多个索引 千万不要忘记指定索引
    MeiliSearch::index('article')->destroy(3);
 ```

- 删除索引

```php
    use \Shopwwi\WebmanMeilisearch\Facade\MeiliSearch;
    MeiliSearch::clear();
    MeiliSearch::index('article')->clear();
 ```

- 数据查询

```php
    use \Shopwwi\WebmanMeilisearch\Facade\MeiliSearch;
    // 基础查询关键词
    $result = MeiliSearch::index('article')->q('我')->get();
    // 全部查询 默认只显示20条
    $result = MeiliSearch::index('article')->get();
    //指定字段(默认指定所有)
    $result = MeiliSearch::index('article')->select(['id','title'])->get();
    //限定查询数量 默认为20
    $result = MeiliSearch::index('article')->limit(100)->q('我')->get();
    //带条件搜索（一定要设定可检索字段 不然无效，一般在初始化新增之后）
    // where($column, $operator = null, $value = null, string $boolean = 'AND') static 搜索
    // orWhere($column, $operator = null, $value = null) static 搜索或者
    
    MeiliSearch::updateFilterableAttributes(['id','title','type','create_at']);
    $result = MeiliSearch::index('article')->where('type','B')->limit(100)->q('我')->get();
    $result = MeiliSearch::index('article')->where('type','!=','B')->limit(100)->q('我')->get();
    $result = MeiliSearch::index('article')->orWhere('type','B')->limit(100)->q('我')->get();
    // whereIn($column, $values, string $boolean = 'AND', bool $not = false) static 存在当中
    // orWhereIn(string $column, $values) static 搜索或者存在当然
    // whereNotIn(string $column,array $values, string $boolean = 'AND') static 搜索不存在当中
    // orWhereNotIn(string $column, $values) static 搜索或者不存在当中
    $result = MeiliSearch::index('article')->whereIn('type',['A','B'])->limit(100)->q('我')->get();
    $result = MeiliSearch::index('article')->whereNotIn('type',['A','B'])->limit(100)->q('我')->get();
    $result = MeiliSearch::index('article')->orWhereIn('type',['A','B'])->limit(100)->q('我')->get();
    $result = MeiliSearch::index('article')->orWhereNotIn('type',['A','B'])->limit(100)->q('我')->get();
    // whereRaw($sql,  $boolean = 'AND') static 原生数据查询
    $result = MeiliSearch::index('article')->where('type','B')->whereRaw('(id = 1 OR id = 2)')->limit(100)->q('我')->get();
    //whereBetween($column,array $values, string $boolean = 'AND')
    $result = MeiliSearch::index('article')->whereBetween('id',[1,5])->limit(100)->q('我')->get();
    //如果您的文档包含_geo数据，您可以使用_geoRadius内置过滤规则根据其地理位置过滤结果
    $result = MeiliSearch::index('article')->where('type','B')->whereRaw('_geoRadius(45.4628328, 9.1076931, 2000)')->limit(100)->q('我')->get();
    
    // 分页
    $result = MeiliSearch::index('article')->where('type','B')->limit(20)->q('我')->paginate(\request()->input('page',1));
    
    //关键词高亮
    $result = MeiliSearch::index('article')->where('type','B')->limit(20)->highlight(['title'])->q('我')->paginate(\request()->input('page',1));

 ```

- 获取指定文档

```php
    use \Shopwwi\WebmanMeilisearch\Facade\MeiliSearch;
    $result = MeiliSearch::index('article')->first(2);
 ```

- 字段排序

```php
    use \Shopwwi\WebmanMeilisearch\Facade\MeiliSearch;
    // 字段排序（一定要设定可排序字段 不然无效，一般在初始化新增之后） 
    // 设定排序字段方法 MeiliSearch::index('article')->updateSortableAttributes(['create_at','id'])
    //1.使用orderBy方法
    $result = MeiliSearch::index('article')->where('type','B')->orderBy('create_at','desc')->orderBy('id')->limit(100)->q('我')->get();
    //2.全局设定
    MeiliSearch::index('article')->updateRankingRules(["words", "typo", "proximity", "attribute", "sort", "exactness","create_at:desc","id:asc"]);
    $result = MeiliSearch::index('article')->where('type','B')->limit(100)->q('我')->get();
 ```

- 任务

```php
    use \Shopwwi\WebmanMeilisearch\Facade\MeiliSearch;
    MeiliSearch::index('article')->getTasks(); // 查询所有任务
    MeiliSearch::index('article')->getTask(1); // 查询指定编号任务
```

- 密钥

```php
    use \Shopwwi\WebmanMeilisearch\Facade\MeiliSearch;
    MeiliSearch::getKeys(); // 查询所有密钥
    MeiliSearch::getKey('d0552b41536279a0ad88bd595327b96f01176a60c2243e906c52ac02375f9bc4'); // 查询指定密钥
    MeiliSearch::createKey([
      'description' => 'Add documents: Products API key',
      'actions' => ['documents.add'],
      'indexes' => ['products'],
      'expiresAt' => '2042-04-02T00:42:42Z',
    ]); //创建密钥
    MeiliSearch::updateKey('d0552b41536279a0ad88bd595327b96f01176a60c2243e906c52ac02375f9bc4',
      [
        'description' => 'Manage documents: Products/Reviews API key',
        'actions' => ['documents.add', 'documents.delete'],
        'indexes' => ['products', 'reviews'],
        'expiresAt' => '2042-04-02T00:42:42Z',
      ]); //更新密钥
    MeiliSearch::deleteKey('d0552b41536279a0ad88bd595327b96f01176a60c2243e906c52ac02375f9bc4'); //删除密钥
```

- 统计

```php
    use \Shopwwi\WebmanMeilisearch\Facade\MeiliSearch;
    MeiliSearch::index('article')->stats(); // 查询统计
    MeiliSearch::index(null)->stats(1); // 查询所有统计
```

- 获取搜索服务器状态

```php
    use \Shopwwi\WebmanMeilisearch\Facade\MeiliSearch;
    MeiliSearch::health();
```

- 获取搜索版本
```php
    use \Shopwwi\WebmanMeilisearch\Facade\MeiliSearch;
    MeiliSearch::version();
```

- 转储
```php
    use \Shopwwi\WebmanMeilisearch\Facade\MeiliSearch;
    MeiliSearch::createDump(); // 创建数据库存储
    MeiliSearch::getDumpStatus('20201101-110357260'); //获取转储状态
```

- 全局配置项目
```php
    use \Shopwwi\WebmanMeilisearch\Facade\MeiliSearch;
    //所有设置
    MeiliSearch::index('article')->getSettings(); // 查询设置
    MeiliSearch::index('article')->updateSettings([]); //更新设置 具体看meilisearch官方文档
    MeiliSearch::index('article')->resetSettings(); //重置设置
    //显示的属性
    MeiliSearch::index('article')->getDisplayedAttributes(); // 查询可显示的字段
    MeiliSearch::index('article')->updateDisplayedAttributes(['*']); //更新可显示的字段
    MeiliSearch::index('article')->resetDisplayedAttributes(); //重置可显示的字段
    //唯一属性即主键
    MeiliSearch::index('article')->getDistinctAttribute(); // 查询主键
    MeiliSearch::index('article')->updateDistinctAttribute('id'); //更新主键
    MeiliSearch::index('article')->resetDistinctAttribute(); //重置主键
    //可过滤属性
    MeiliSearch::index('article')->getFilterableAttributes(); // 查询可过滤字段
    MeiliSearch::index('article')->updateFilterableAttributes(['id','type']); //更新可过滤字段
    MeiliSearch::index('article')->resetFilterableAttributes(); //重置可过滤字段
    //排序规则
    MeiliSearch::index('article')->getRankingRules(); // 查询排序规则
    MeiliSearch::index('article')->updateRankingRules(['words','typo','proximity','attribute','sort','exactness','create_at:desc']); //更新排序规则
    MeiliSearch::index('article')->resetRankingRules(); //重置排序规则
    //可搜索的字段
    MeiliSearch::index('article')->getSearchableAttributes(); // 查询可搜索字段
    MeiliSearch::index('article')->updateSearchableAttributes(['title']); //更新可搜索字段
    MeiliSearch::index('article')->resetSearchableAttributes(); //重置可搜索字段
    //可排序的字段
    MeiliSearch::index('article')->getSortableAttributes(); // 查询可排序字段
    MeiliSearch::index('article')->updateSortableAttributes(['id','create_at']); //更新可排序字段
    MeiliSearch::index('article')->resetSortableAttributes(); //重置可排序字段
    //停用词
    MeiliSearch::index('article')->getStopWords(); // 查询停用词
    MeiliSearch::index('article')->updateStopWords(['the', 'of', 'to']); //更新停用词
    MeiliSearch::index('article')->resetStopWords(); //重置停用词
    //同义词
    MeiliSearch::index('article')->getSynonyms(); // 查询同义词
    MeiliSearch::index('article')->updateSynonyms([
      '帅' => ['帅哥', '帅小伙'],
      '哥' => ['帅', '帅哥'],
      '美好' => ['美好的世界']
    ]); //更新同义词
    MeiliSearch::index('article')->resetSynonyms(); //重置同义词
 ```