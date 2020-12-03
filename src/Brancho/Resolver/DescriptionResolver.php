<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Brancho\Resolver;

use Brancho\Context\ContextInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class DescriptionResolver implements ResolverInterface
{
    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Brancho\Context\ContextInterface $context
     *
     * @return array|null
     */
    public function resolve(InputInterface $input, OutputInterface $output, ContextInterface $context): ?array
    {
        $question = new Question('Please enter the description text to be used: ');
        $helper = new QuestionHelper();

        return (array)$context->getFilter()->filter($helper->ask($input, $output, $question));
    }
}
