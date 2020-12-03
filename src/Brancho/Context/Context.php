<?php

namespace Brancho\Context;

use Laminas\Filter\FilterInterface;

class Context implements ContextInterface
{
    /**
     * @var array|null
     */
    protected $config;

    /**
     * @var FilterInterface|null
     */
    protected $filter;

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
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
     * @return FilterInterface
     */
    public function getFilter(): FilterInterface
    {
        return $this->filter;
    }

    /**
     * @param FilterInterface $filter
     *
     * @return void
     */
    public function setFilter(FilterInterface $filter): void
    {
        $this->filter = $filter;
    }
}
