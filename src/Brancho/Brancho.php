<?php

namespace Brancho;

use Brancho\Command\BranchBuilderCommand;
use Brancho\Config\Config;
use Brancho\Config\ConfigInterface;
use Brancho\Context\Context;
use Brancho\Resolver\AbstractResolver;
use Brancho\Resolver\ResolverInterface;
use Laminas\Filter\FilterChain;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Brancho
{
    /**
     * @var string[]
     */
    protected $resolvedElements = [];

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var BranchoFactory
     */
    protected $factory;

    /**
     * @param ConfigInterface $config
     * @param BranchoFactory $factory
     */
    public function __construct(ConfigInterface $config, BranchoFactory $factory)
    {
        $this->config = $config;
        $this->factory = $factory;
    }

    /**
     * @return string
     */
    public function resolveBranchName(InputInterface $input, OutputInterface $output): string
    {
        $config = $this->loadConfig($this->getConfigPath($input));

        $pattern = $config[Config::PATTERN];

        $context = new Context();
        $context->setConfig($config);
        $context->setFilter($this->getFilter($config));

        preg_match_all('/{.*?}/', $pattern, $matches, PREG_PATTERN_ORDER);

        $resolvedParts = [];

        foreach ($matches[0] as $match) {
            $resolver = $this->getResolverByIdentifier($match, $config);
            $resolvedParts[$match] = $resolver->resolve($input, $output, $context);
        }

        return str_replace(array_keys($resolvedParts), array_values($resolvedParts), $pattern);
    }

    /**
     * @param InputInterface $input
     *
     * @return string
     */
    protected function getConfigPath(InputInterface $input): string
    {
        /** @var string $configPath */
        $configPath = $input->getOption(BranchBuilderCommand::CONFIG);

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
     * @param string $resolverIdentifier
     * @param array $config
     *
     * @return ResolverInterface
     */
    protected function getResolverByIdentifier(string $resolverIdentifier, array $config): ResolverInterface
    {
        $resolver = new $config[Config::RESOLVERS][str_replace(['{', '}'], '', $resolverIdentifier)]();

        if ($resolver instanceof AbstractResolver) {
            $resolver->setFactory($this->factory);
        }

        return $resolver;
    }

    /**
     * @param array $config
     *
     * @return FilterChain
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
