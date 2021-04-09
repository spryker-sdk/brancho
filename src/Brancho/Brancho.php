<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Brancho;

use Brancho\Command\BranchBuilderCommand;
use Brancho\Config\Config;
use Brancho\Config\ConfigInterface;
use Brancho\Resolver\AbstractResolver;
use Brancho\Resolver\ConfigurableResolverInterface;
use Brancho\Resolver\ResolverInterface;
use Laminas\Filter\FilterChain;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Brancho
{
    /**
     * @var \Brancho\Config\ConfigInterface
     */
    protected $config;

    /**
     * @var \Brancho\BranchoFactory
     */
    protected $factory;

    /**
     * @param \Brancho\Config\ConfigInterface $config
     * @param \Brancho\BranchoFactory $factory
     */
    public function __construct(ConfigInterface $config, BranchoFactory $factory)
    {
        $this->config = $config;
        $this->factory = $factory;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return array|null
     */
    public function resolveBranchNames(InputInterface $input, OutputInterface $output): ?array
    {
        $config = $this->loadConfig($this->getConfigPath($input));

        $context = $this->factory->createContext();
        $context->setConfig($config);
        $context->setFilter($this->getFilter($config));

        $resolver = $this->getResolver($config);

        return $resolver->resolve($input, $output, $context);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return string|null
     */
    public function resolveCommitMessage(InputInterface $input, OutputInterface $output): ?string
    {
        $config = $this->loadConfig($this->getConfigPath($input));

        $context = $this->factory->createContext();
        $context->setConfig($config);

        return $this->factory->createCommitMessageResolver()->resolve($input, $output, $context);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     *
     * @return string
     */
    protected function getConfigPath(InputInterface $input): string
    {
        /** @var string $configPath */
        $configPath = $input->getOption(BranchBuilderCommand::OPTION_CONFIG);

        return $configPath;
    }

    /**
     * @param string $pathToConfig
     *
     * @return array
     */
    protected function loadConfig(string $pathToConfig): array
    {
        return $this->config->load($pathToConfig);
    }

    /**
     * @param array $config
     *
     * @return \Brancho\Resolver\ResolverInterface
     */
    protected function getResolver(array $config): ResolverInterface
    {
        $resolver = new $config[Config::RESOLVER]();

        if ($resolver instanceof AbstractResolver) {
            $resolver->setFactory($this->factory);
        }

        if ($resolver instanceof ConfigurableResolverInterface) {
            $decoratedResolver = $this->factory->createResolverDecorator();
            $decoratedResolver->setResolver($resolver);

            return $decoratedResolver;
        }

        return $resolver;
    }

    /**
     * @param array $config
     *
     * @return \Laminas\Filter\FilterChain
     */
    protected function getFilter(array $config): FilterChain
    {
        $filterChain = new FilterChain();
        foreach ($config[Config::FILTERS] as $filter) {
            $filterChain->attach(new $filter());
        }

        return $filterChain;
    }
}
