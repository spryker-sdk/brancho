<?php

namespace Brancho\Resolver;

use Brancho\BranchoFactory;

abstract class AbstractResolver implements ResolverInterface
{
    /**
     * @var BranchoFactory
     */
    protected $factory;

    /**
     * @return BranchoFactory
     */
    public function getFactory(): BranchoFactory
    {
        return $this->factory;
    }

    /**
     * @param BranchoFactory $factory
     *
     * @return void
     */
    public function setFactory(BranchoFactory $factory): void
    {
        $this->factory = $factory;
    }
}
