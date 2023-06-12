<?php

namespace Shopwwi\WebmanSearch\Command;

use Shopwwi\WebmanSearch\Facade\Search;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;


class SearchUpdateIndexCommand extends Command
{
    protected static $defaultName = 'search:update:index';
    protected static $defaultDescription = '执行搜索索引更新';

    /**
     * @return void
     */
    protected function configure()
    {
        $this->addArgument('index', InputArgument::OPTIONAL, '索引驱动器');
        $this->addOption('holder', 'holder', InputOption::VALUE_REQUIRED, '索引名称');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $holder = $input->getOption('holder');
        $holder = $holder ? $holder : config('plugin.shopwwi.search.app.default');

        if($holder == 'elasticsearch'){ // 创建es索引
            $indices = !is_null($input->getArgument('index')) ? [$input->getArgument('index')] :
                array_keys(config('plugin.shopwwi.search.app.holder.elasticsearch.indices'));
            foreach ($indices as $index) {
                $search = Search::use($holder,['index'=>$index])->us();
                $config = config('plugin.shopwwi.search.app.holder.elasticsearch.indices')[$index];
                if (is_null($index)) {
                    $output->writeln("Missing configuration for index: {$index}");
                    continue;
                }
                $have = $search->indices()->exists(['index' => $index]);
                if (!$have->asBool()) {
                    $output->writeln("Index: {$index} is not exist!");
                    continue;
                }
                $output->writeln("Removing aliases for index: {$index}");
                $search->indices()->updateAliases([
                    "body" => [
                        'actions' => [
                            [
                                'remove' => [
                                    'index' => $index,
                                    'alias' => "*"
                                ]
                            ]
                        ]
                    ],
                    'client' => ['ignore' => [404]]
                ]);
                if (isset($config['aliases'])) {

                    // Update index aliases from config

                    foreach ($config['aliases'] as $alias) {
                        $output->writeln("Creating alias: {$alias} for index: {$index}");
                        $search->indices()->updateAliases([
                            "body" => [
                                'actions' => [
                                    [
                                        'add' => [
                                            'index' => $index,
                                            'alias' => $alias
                                        ]
                                    ]
                                ]

                            ]
                        ]);
                    }
                }

                if (isset($config['mappings'])) {
                    foreach ($config['mappings'] as $type => $mapping) {
                        // Create mapping for type from config file
                        $output->writeln("Creating mapping for type: {$type} in index: {$index}");
                        $search->indices()->putMapping([
                            'index' => $index,
                            'type' => $type,
                            'body' => $mapping,
                            "include_type_name" => true
                        ]);
                    }
                }
            }
        }else if($holder == 'meilisearch'){
            $indices = !is_null($input->getArgument('index')) ? [$input->getArgument('index')] :
                array_keys(config('plugin.shopwwi.search.app.holder.meilisearch.indices'));
            foreach ($indices as $index) {
                $config = config('plugin.shopwwi.search.app.holder.meilisearch.indices')[$index];
                $search = Search::use($holder,['index'=>$index])->us();
                // 更新可显示字段
                $output->writeln("set Sortable attributes");
                $search->index($index)->updateSortableAttributes($config['sortable']);
                $output->writeln("set RankingRules");
                $search->index($index)->updateRankingRules($config['rank']);
                $output->writeln("set filterable attributes");
                $search->index($index)->updateFilterableAttributes($config['filterable']); // 可检索字段
            }
        }
        $output->writeln('索引更改结束');
        return self::SUCCESS;
    }

}
