<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Brancho;

use Brancho\Commit\CommitMessageResolver;
use Brancho\Commit\CommitMessageResolverInterface;
use Brancho\Config\Config;
use Brancho\Config\ConfigInterface;
use Brancho\Config\Reader\ConfigReader;
use Brancho\Config\Reader\ConfigReaderInterface;
use Brancho\Context\Context;
use Brancho\Context\ContextInterface;
use Brancho\Jira\Jira;
use Brancho\Resolver\ResolverDecorator;
use Brancho\Resolver\ResolverDecoratorInterface;

class BranchoFactory
{
    /**
     * @return \Brancho\Brancho
     */
    public function createBrancho(): Brancho
    {
        return new Brancho($this->createConfig(), $this);
    }

    /**
     * @return \Brancho\Config\ConfigInterface
     */
    public function createConfig(): ConfigInterface
    {
        return new Config($this->createConfigReader());
    }

    /**
     * @return \Brancho\Context\ContextInterface
     */
    public function createContext(): ContextInterface
    {
        return new Context();
    }

    /**
     * @return \Brancho\Config\Reader\ConfigReaderInterface
     */
    public function createConfigReader(): ConfigReaderInterface
    {
        return new ConfigReader();
    }

    /**
     * @codeCoverageIgnore Jira uses only mocks for testing.
     *
     * @return \Brancho\Jira\Jira
     */
    public function createJira(): Jira
    {
        return new Jira();
    }

    /**
     * @return \Brancho\Resolver\ResolverDecoratorInterface
     */
    public function createResolverDecorator(): ResolverDecoratorInterface
    {
        return new ResolverDecorator();
    }

    /**
     * @codeCoverageIgnore CommitMessageResolver uses only mocks for testing.
     *
     * @return \Brancho\Commit\CommitMessageResolverInterface
     */
    public function createCommitMessageResolver(): CommitMessageResolverInterface
    {
        return new CommitMessageResolver();
    }
}
