<?php

namespace Brancho\Config\Reader;

use Symfony\Component\Yaml\Yaml;

class ConfigReader implements ConfigReaderInterface
{
    /**
     * @param string $configPath
     *
     * @return array
     */
    public function read(string $configPath): array
    {
        $config = Yaml::parse($this->getFileContent($configPath));

        return $config;
    }

    /**
     * @param string $configPath
     *
     * @return string
     */
    protected function getFileContent(string $configPath): string
    {
        /** @var string $fileContent */
        $fileContent = file_get_contents($configPath);

        return $fileContent;
    }
}
