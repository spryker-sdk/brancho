<?php

namespace Brancho\Resolver;

use Brancho\Context\ContextInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface ResolverInterface
{
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param ContextInterface $context
     *
     * @return string|null
     */
    public function resolve(InputInterface $input, OutputInterface $output, ContextInterface $context): ?string;
}
