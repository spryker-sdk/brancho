<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Brancho\Context;

use Laminas\Filter\FilterChain;
use Laminas\Filter\FilterInterface;

class Context implements ContextInterface
{
    /**
     * @var array|null
     */
    protected $config;

    /**
     * @var \Laminas\Filter\FilterInterface|null
     */
    protected $filter;

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config ?? [];
    }

    /**
     * @param array $config
     *
     * @return void
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * @return \Laminas\Filter\FilterInterface
     */
    public function getFilter(): FilterInterface
    {
        return $this->filter ?? new FilterChain();
    }

    /**
     * @param \Laminas\Filter\FilterInterface $filter
     *
     * @return void
     */
    public function setFilter(FilterInterface $filter): void
    {
        $this->filter = $filter;
    }
}
