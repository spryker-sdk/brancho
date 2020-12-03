<?php

namespace Brancho\Config\Reader;

interface ConfigReaderInterface
{
    /**
     * @param string $configPath
     *
     * @return array
     */
    public function read(string $configPath): array;
}
