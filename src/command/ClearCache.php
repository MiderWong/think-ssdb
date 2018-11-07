<?php
namespace think\cache\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\Option;

class ClearCache extends Command
{
    public function configure()
    {
        $this->setName('ssdb:cache:clear')->setDescription('清理SSDB缓存');
    }

    public function execute(Input $input, Output $output)
    {

    }
}
