<?php

namespace BranchoTest\Helper;

use Brancho\BranchoBootstrap;
use Codeception\Module;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class CommandHelper extends Module
{
    /**
     * @return void
     */
    public function _initialize(): void
    {
        defined('ROOT_DIR') || define('ROOT_DIR', getcwd());
    }

    /**
     * @param \Symfony\Component\Console\Command\Command $command
     *
     * @return \Symfony\Component\Console\Tester\CommandTester
     */
    public function getConsoleTester(Command $command): CommandTester
    {
        $application = new BranchoBootstrap();
        $application->add($command);

        $command = $application->find($command->getName());

        return new CommandTester($command);
    }
}
