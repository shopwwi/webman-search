<?php

namespace app\command;

use Shopwwi\WebmanSearch\Facade\Search;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;


class SearchDropIndexCommand extends Command
{
    protected static $defaultName = 'search:drop:index';
    protected static $defaultDescription = '执行搜索索引清空';

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
              //  $config = config('plugin.shopwwi.search.app.holder.elasticsearch.indices')[$index];
                if (is_null($index)) {
                    $output->writeln("Missing configuration for index: {$index}");
                    continue;
                }
                $have = $search->indices()->exists(['index' => $index]);
                if (!$have->asBool()) {
                    $output->writeln("Index: {$index} is not exist!");
                    continue;
                }
                $search->indices()->delete(['index' => $index]);
            }
        }else if($holder == 'meilisearch'){
            $indices = !is_null($input->getArgument('index')) ? [$input->getArgument('index')] :
                array_keys(config('plugin.shopwwi.search.app.holder.meilisearch.indices'));
            foreach ($indices as $index) {
                $search = Search::use($holder,['index'=>$index])->us();
                $search->delete();
            }
        }else if($holder == 'xunsearch'){
            $indices = !is_null($input->getArgument('index')) ? [$input->getArgument('index')] :
                array_keys(config('plugin.shopwwi.search.app.holder.xunsearch.indices'));
            foreach ($indices as $index) {
                $search = Search::use($holder,['index'=>$index])->us();
                $search->delete();
            }
        }
            $output->writeln('索引删除结束');
        return self::SUCCESS;
    }

}
