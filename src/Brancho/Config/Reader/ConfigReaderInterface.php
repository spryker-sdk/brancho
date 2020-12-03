<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

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
