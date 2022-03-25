<?php

namespace app\command;

use Shopwwi\WebmanMeilisearch\Facade\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;


class ShopwwiMeilisearchCommand extends Command
{
    protected static $defaultName = 'shopwwi:meilisearch';
    protected static $defaultDescription = 'shopwwi meilisearch';

    /**
     * @return void
     */
    protected function configure()
    {
        $this->addArgument('name', InputArgument::OPTIONAL, 'Name description');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $output->writeln('生成meilisearch密钥 开始');
        $key = Str::random(64);
        file_put_contents(base_path()."/config/plugin/shopwwi/meilisearch/app.php", str_replace(
            "'key' => '".config('plugin.shopwwi.meilisearch.app.key')."'",
            "'key' => '".$key."'",
            file_get_contents(base_path()."/config/plugin/shopwwi/meilisearch/app.php")
        ));
        $output->writeln('生成meilisearch密钥 结束'.$key);
        return self::SUCCESS;
    }

}
