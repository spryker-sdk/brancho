<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Brancho\Command;

use Brancho\BranchoFactory;
use Symfony\Component\Console\Command\Command;

class AbstractCommand extends Command
{
    /**
     * @var int
     */
    protected const CODE_SUCCESS = 0;

    /**
     * @var int
     */
    protected const CODE_ERROR = 1;

    /**
     * @var \Brancho\BranchoFactory|null
     */
    protected $factory;

    /**
     * @param \Brancho\BranchoFactory $factory
     *
     * @return void
     */
    public function setFactory(BranchoFactory $factory): void
    {
        $this->factory = $factory;
    }

    /**
     * @return \Brancho\BranchoFactory
     */
    protected function getFactory(): BranchoFactory
    {
        if ($this->factory === null) {
            $this->factory = new BranchoFactory();
        }

        return $this->factory;
    }
}
