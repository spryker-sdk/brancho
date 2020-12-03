<?php

namespace Brancho\Config;

use Brancho\Config\Reader\ConfigReaderInterface;
use Brancho\Resolver\DescriptionResolver;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Config implements ConfigInterface
{
    public const PATTERN = 'pattern';
    public const RESOLVERS = 'resolvers';
    public const FILTERS = 'filters';
    public const JIRA = 'jira';

    /**
     * @var array
     */
    protected $config;

    /**
     * @var ConfigReaderInterface
     */
    protected $configReader;

    /**
     * @param ConfigReaderInterface $configReader
     */
    public function __construct(ConfigReaderInterface $configReader)
    {
        $this->configReader = $configReader;
    }

    /**
     * @param string $pathToConfig
     *
     * @return array
     */
    public function load(string $pathToConfig): array
    {
        if ($this->config === null) {
            $config = $this->getRootConfiguration($pathToConfig);
            $config = $this->mergeLocalConfigurations($pathToConfig, $config);

            $this->config = $this->getOptionResolver()->resolve($config);
        }

        return $this->config;
    }

    /**
     * @param string $pathToConfig
     *
     * @return array
     */
    protected function getRootConfiguration(string $pathToConfig): array
    {
        $config = [];

        if (file_exists($pathToConfig)) {
            $config = $this->configReader->read($pathToConfig);
        }

        return $config;
    }

    /**
     * @param string $pathToConfig
     * @param array $config
     *
     * @return array
     */
    protected function mergeLocalConfigurations(string $pathToConfig, array $config): array
    {
        $localConfigurationPaths = [
            dirname($pathToConfig) . '/.brancho.local',
            $this->getHomeDirectory() . '/brancho/.brancho.local',
        ];

        foreach ($localConfigurationPaths as $localConfigurationPath) {
            if (file_exists($localConfigurationPath)) {
                $localConfig = $this->configReader->read($localConfigurationPath);
                $config = array_merge($config, $localConfig);
            }
        }

        return $config;
    }

    /**
     * @return string
     */
    protected function getHomeDirectory(): string
    {
        return (string)getenv('HOME');
    }

    /**
     * @return OptionsResolver
     */
    private function getOptionResolver(): OptionsResolver
    {
        $optionResolver = new OptionsResolver();
        $optionResolver->setDefaults([
            static::PATTERN => '{description}',
            static::RESOLVERS => [
                '{description}' => DescriptionResolver::class,
            ],
            static::FILTERS => [],
            static::JIRA => [
                'host' => '',
                'username' => '',
                'password' => '',
            ],
        ]);

        return $optionResolver;
    }
}
