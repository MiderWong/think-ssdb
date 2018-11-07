<?php
namespace think\cache\driver\command;

use think\Cache;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

class ClearCache extends Command
{
    public function configure()
    {
        $this->setName('ssdb:cache:clear')->setDescription('清理SSDB缓存');
        $this->addOption('prefix', 'p', Option::VALUE_OPTIONAL, '缓存前缀', config('cache.prefix'));
    }

    public function execute(Input $input, Output $output)
    {
        $prefix = $input->getOption('prefix');
        $cache = Cache::init(['type' => 'Ssdb', 'prefix' => $prefix] + config('cache'));
        $handler = $cache->handler();

        $lastKey = '';
        while ($keys = $handler->keys($lastKey, '', 5)) {
            foreach ($keys as $k) {
                if (\strpos($k, $prefix) === 0) {
                    $handler->del($k);
                    $output->writeln('<info>Deleting key: </info>' . $k);
                }
                $lastKey = $k;
            }
        }
    }
}
