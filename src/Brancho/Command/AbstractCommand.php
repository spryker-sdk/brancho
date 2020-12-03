<?php

namespace Brancho\Command;

use Brancho\BranchoFactory;
use Symfony\Component\Console\Command\Command;

class AbstractCommand extends Command
{
    protected const CODE_SUCCESS = 0;
    protected const CODE_ERROR = 1;

    /**
     * @var BranchoFactory|null
     */
    protected $factory;

    /**
     * @param BranchoFactory $factory
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
