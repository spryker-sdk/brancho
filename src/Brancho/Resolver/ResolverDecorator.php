<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Brancho\Resolver;

use Brancho\Command\BranchBuilderCommand;
use Brancho\Context\ContextInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

/**
 * If a resolver needs configuration and the configuration doesn't exists for this resolver
 * this decorator will take care of getting the required information and will write it to the local configuration.
 */
class ResolverDecorator implements ResolverDecoratorInterface
{
    /**
     * @var \Brancho\Resolver\ConfigurableResolverInterface
     */
    protected $resolver;

    /**
     * @param \Brancho\Resolver\ConfigurableResolverInterface $resolver
     *
     * @return void
     */
    public function setResolver(ConfigurableResolverInterface $resolver): void
    {
        $this->resolver = $resolver;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Brancho\Context\ContextInterface $context
     *
     * @return array|null
     */
    public function resolve(InputInterface $input, OutputInterface $output, ContextInterface $context): ?array
    {
        $context = $this->getAddResolverConfigurationToContext($input, $output, $context);

        return $this->resolver->resolve($input, $output, $context);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Brancho\Context\ContextInterface $context
     *
     * @return \Brancho\Context\ContextInterface
     */
    protected function getAddResolverConfigurationToContext(InputInterface $input, OutputInterface $output, ContextInterface $context): ContextInterface
    {
        if (!$this->resolver->isConfigured($context)) {
            $config = $this->buildConfig($input, $output);
            $context->setConfig(array_merge($context->getConfig(), $config));
        }

        return $context;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return array
     */
    protected function buildConfig(InputInterface $input, OutputInterface $output): array
    {
        $configurationInformation = $this->resolver->getConfigurationInformation();

        $resolverConfigurationKey = array_keys($configurationInformation)[0];
        $requiredConfiguration = current($configurationInformation);

        $config = [];

        foreach ($requiredConfiguration as $configurationKey => $question) {
            $helper = new QuestionHelper();

            $answer = $helper->ask($input, $output, $this->getQuestion($question));
            $config[$configurationKey] = $answer;
        }

        $config = [
            $resolverConfigurationKey => $config,
        ];

        $this->writeLocalConfiguration($this->getRootDirectory($input), $config);

        return $config;
    }

    /**
     * @param array|string $question
     *
     * @return \Symfony\Component\Console\Question\Question
     */
    protected function getQuestion($question): Question
    {
        if (is_array($question)) {
            $default = $question['default'] ?? '';

            return new Question(sprintf('%s: ', $question['question']), $default);
        }

        return new Question(sprintf('%s: ', $question));
    }

    /**
     * @codeCoverageIgnore
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     *
     * @return string
     */
    protected function getRootDirectory(InputInterface $input): string
    {
        /** @var string $configPath */
        $configPath = $input->getOption(BranchBuilderCommand::OPTION_CONFIG);

        return dirname($configPath);
    }

    /**
     * @param string $root
     * @param array $resolverConfiguration
     *
     * @return void
     */
    protected function writeLocalConfiguration(string $root, array $resolverConfiguration): void
    {
        $localConfigurationFile = sprintf('%s/.brancho.local', rtrim($root, DIRECTORY_SEPARATOR));

        if (file_exists($localConfigurationFile)) {
            $localConfiguration = Yaml::parse((string)file_get_contents($localConfigurationFile));
            $localConfiguration = array_merge($localConfiguration, $resolverConfiguration);

            file_put_contents($localConfigurationFile, Yaml::dump($localConfiguration));

            return;
        }

        file_put_contents($localConfigurationFile, Yaml::dump($resolverConfiguration));
    }
}
