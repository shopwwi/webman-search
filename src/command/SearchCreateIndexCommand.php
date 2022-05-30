<?php

namespace app\command;

use Shopwwi\WebmanSearch\Facade\Search;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;


class SearchCreateIndexCommand extends Command
{
    protected static $defaultName = 'search:create:index';
    protected static $defaultDescription = '执行搜索索引初始化';

    /**
     * @return void
     */
    protected function configure()
    {
        $this->addArgument('index', InputArgument::OPTIONAL, '索引名称');
        $this->addOption('holder', 'holder', InputOption::VALUE_REQUIRED, '索引驱动器');
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
                $search = Search::use($holder,['index' => $index])->us();
                $config = config('plugin.shopwwi.search.app.holder.elasticsearch.indices')[$index];
                if (is_null($index)) {
                    $output->writeln("Missing configuration for index: {$index}");
                    continue;
                }
                $have = $search->indices()->exists(['index' => $index]);
                if ($have->asBool()) {
                    $output->writeln("Index {$index} is already exists!");
                    continue;
                }
                $search->indices()->create([
                    'index' => $index,
                    'body' => [
                        "settings" => $config['settings']
                    ]
                ]);
                if (isset($config['aliases'])) {
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
        }else if($holder == 'meilisearch'){ // 不需要

        }
        $output->writeln('索引创建结束');
        return self::SUCCESS;
    }

}
