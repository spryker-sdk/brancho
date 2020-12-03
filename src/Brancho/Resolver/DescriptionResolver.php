<?php

namespace Brancho\Resolver;

use Brancho\Context\ContextInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class DescriptionResolver implements ResolverInterface
{
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param ContextInterface $context
     *
     * @return string|null
     */
    public function resolve(InputInterface $input, OutputInterface $output, ContextInterface $context): ?string
    {
        $question = new Question('Please enter the description text to be used: ');
        $helper = new QuestionHelper();

        return $context->getFilter()->filter($helper->ask($input, $output, $question));
    }
}
