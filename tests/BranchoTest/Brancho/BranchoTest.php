<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace BranchoTest\Brancho;

use Brancho\Brancho;
use Brancho\BranchoFactory;
use Brancho\Config\Config;
use Brancho\Config\Reader\ConfigReader;
use Codeception\Test\Unit;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @group Brnacho
 */
class BranchoTest extends Unit
{
    /**
     * @return void
     */
    public function testLoadNonExistentConfigFileThrowsException(): void
    {
        $input = $this->makeEmpty(InputInterface::class, [
            'getOption' => function () {
                return 'does-not-exist';
            },
        ]);

        $output = $this->makeEmpty(OutputInterface::class);
        $config = new Config(new ConfigReader());

        $brancho = new Brancho($config, new BranchoFactory());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Config file `does-not-exist` does not exist');

        $brancho->resolveBranchNames($input, $output);
    }
}
