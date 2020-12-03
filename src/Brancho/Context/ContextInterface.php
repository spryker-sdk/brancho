<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Brancho\Context;

use Laminas\Filter\FilterInterface;

interface ContextInterface
{
    /**
     * @return array
     */
    public function getConfig(): array;

    /**
     * @param array $config
     *
     * @return void
     */
    public function setConfig(array $config): void;

    /**
     * @return \Laminas\Filter\FilterInterface
     */
    public function getFilter(): FilterInterface;

    /**
     * @param \Laminas\Filter\FilterInterface $filter
     *
     * @return void
     */
    public function setFilter(FilterInterface $filter): void;
}
