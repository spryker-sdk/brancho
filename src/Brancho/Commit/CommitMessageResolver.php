<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Brancho\Commit;

use Brancho\Command\CommitCommand;
use Brancho\Context\ContextInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Process;

class CommitMessageResolver implements CommitMessageResolverInterface
{
    public const WRITE_OWN = 'write own';

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Brancho\Context\ContextInterface $context
     *
     * @return string|null
     */
    public function resolve(InputInterface $input, OutputInterface $output, ContextInterface $context): ?string
    {
        $branchName = $this->getBranchName();

        if (preg_match_all('/([a-z]+\-[0-9]+)/', $branchName, $matches, PREG_SET_ORDER)) {
            $message = rtrim($this->getMessage($input, $output, $context), '.');

            return sprintf('%s %s.', strtoupper(array_pop($matches)[0]), $message);
        }

        return null;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return string
     */
    protected function getBranchName(): string
    {
        $process = new Process(['git', 'rev-parse', '--abbrev-ref', 'HEAD'], (string)getcwd());
        $process->run();

        return trim($process->getOutput());
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Brancho\Context\ContextInterface $context
     *
     * @return string
     */
    protected function getMessage(InputInterface $input, OutputInterface $output, ContextInterface $context): string
    {
        if ($input->getOption(CommitCommand::OPTION_MESSAGE)) {
            /** @var string $message */
            $message = $input->getOption(CommitCommand::OPTION_MESSAGE);

            return $message;
        }

        $messages = $context->getConfig()['commit']['messages'] ?? [];
        $messages[] = static::WRITE_OWN;

        $helper = new QuestionHelper();
        $question = new ChoiceQuestion(
            sprintf('Please select from default messages (defaults to "%s")', static::WRITE_OWN),
            $messages,
            count($messages) - 1
        );

        $message = $helper->ask($input, $output, $question);

        if ($message === 'write own') {
            $question = new Question('Please type your messages: ');

            $message = $helper->ask($input, $output, $question);
        }

        return $message;
    }
}
