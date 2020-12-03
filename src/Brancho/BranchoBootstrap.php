<?php

namespace Brancho;

use Brancho\Command\BranchBuilderCommand;
use Brancho\Command\InitCommand;
use Symfony\Component\Console\Application;

class BranchoBootstrap extends Application
{
    /**
     * @param string $name
     * @param string $version
     */
    public function __construct($name = 'Brancho', $version = '1')
    {
        parent::__construct($name, $version);

        $this->setCatchExceptions(false);
    }

    /**
     * @return \Symfony\Component\Console\Command\Command[]
     */
    protected function getDefaultCommands(): array
    {
        $commands = parent::getDefaultCommands();

        foreach ($this->getCommands() as $command) {
            $commands[$command->getName()] = $command;
        }

        return $commands;
    }

    /**
     * @return \Symfony\Component\Console\Command\Command[]
     */
    private function getCommands(): array
    {
        return [
            new BranchBuilderCommand(),
            new InitCommand(),
        ];
    }
}
