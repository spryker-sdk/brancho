<?php

namespace Brancho;

use Brancho\Config\Config;
use Brancho\Config\ConfigInterface;
use Brancho\Config\Reader\ConfigReader;
use Brancho\Config\Reader\ConfigReaderInterface;
use Brancho\Jira\Jira;

class BranchoFactory
{
    /**
     * @return Brancho
     */
    public function createBrancho(): Brancho
    {
        return new Brancho($this->createConfig(), $this);
    }

    /**
     * @return ConfigInterface
     */
    public function createConfig(): ConfigInterface
    {
        return new Config($this->createConfigReader());
    }

    /**
     * @return ConfigReaderInterface
     */
    public function createConfigReader(): ConfigReaderInterface
    {
        return new ConfigReader();
    }

    /**
     * @codeCoverageIgnore Jira uses only mocks for testing.
     *
     * @return Jira
     */
    public function createJira(): Jira
    {
        return new Jira();
    }
}
