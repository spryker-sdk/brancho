<?php

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
     * @return FilterInterface
     */
    public function getFilter(): FilterInterface;

    /**
     * @param FilterInterface $filter
     *
     * @return void
     */
    public function setFilter(FilterInterface $filter): void;
}
