<?php

namespace Brancho\Resolver;

use Brancho\Context\ContextInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class TypeResolver implements ResolverInterface
{
    /**
     * @var string[]
     */
    protected $types = [
        'feature',
        'bugfix',
    ];

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param ContextInterface $context
     *
     * @return string|null
     */
    public function resolve(InputInterface $input, OutputInterface $output, ContextInterface $context): ?string
    {
        $question = new ChoiceQuestion('Please select the type to be used: ', $this->types);
        $helper = new QuestionHelper();

        return $helper->ask($input, $output, $question);
    }
}
