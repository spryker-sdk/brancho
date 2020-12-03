<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Brancho\Config;

use Brancho\Config\Reader\ConfigReaderInterface;

class Config implements ConfigInterface
{
    public const RESOLVER = 'resolver';
    public const FILTERS = 'filters';

    /**
     * @var array
     */
    protected $config;

    /**
     * @var \Brancho\Config\Reader\ConfigReaderInterface
     */
    protected $configReader;

    /**
     * @param \Brancho\Config\Reader\ConfigReaderInterface $configReader
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

            $this->config = $config;
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
        $localConfiguration = dirname($pathToConfig) . '/.brancho.local';

        if (file_exists($localConfiguration)) {
            $localConfig = $this->configReader->read($localConfiguration);
            $config = array_merge($config, $localConfig);
        }

        return $config;
    }
}
