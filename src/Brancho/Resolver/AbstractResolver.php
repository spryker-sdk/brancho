<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Brancho\Resolver;

use Brancho\BranchoFactory;

abstract class AbstractResolver implements ResolverInterface
{
    /**
     * @var \Brancho\BranchoFactory
     */
    protected $factory;

    /**
     * @return \Brancho\BranchoFactory
     */
    public function getFactory(): BranchoFactory
    {
        return $this->factory;
    }

    /**
     * @param \Brancho\BranchoFactory $factory
     *
     * @return void
     */
    public function setFactory(BranchoFactory $factory): void
    {
        $this->factory = $factory;
    }
}
