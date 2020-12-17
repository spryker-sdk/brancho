<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Brancho\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Process;

class CommitCommand extends AbstractCommand
{
    public const OPTION_MESSAGE_SHORT = 'm';
    public const OPTION_MESSAGE = 'message';
    public const OPTION_CONFIG = 'config';
    public const OPTION_CONFIG_SHORTCUT = 'c';
    public const OPTION_COMMIT_ALL = 'all';
    public const OPTION_COMMIT_ALL_SHORT = 'a';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('commit')
            ->addOption(static::OPTION_MESSAGE, static::OPTION_MESSAGE_SHORT, InputOption::VALUE_REQUIRED, 'Message used for the commit.')
            ->addOption(static::OPTION_COMMIT_ALL, static::OPTION_COMMIT_ALL_SHORT, InputOption::VALUE_NONE, 'Commit all changed files.')
            ->addOption(
                static::OPTION_CONFIG,
                static::OPTION_CONFIG_SHORTCUT,
                InputOption::VALUE_REQUIRED,
                'Path to a configuration file (default: .brancho)',
                ROOT_DIR . '/.brancho'
            )
            ->setDescription('Commits branch changes and prefixes the commit message with the current Jira issue.');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $brancho = $this->getFactory()->createBrancho();
        $resolvedCommitMessage = $brancho->resolveCommitMessage($input, $output);

        if (!$resolvedCommitMessage) {
            $output->writeln('<fg=red>The resolved commit message is empty, something went wrong.</>');

            return static::CODE_ERROR;
        }

        $this->commit($input, $output, $resolvedCommitMessage);

        return static::CODE_SUCCESS;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string $resolvedCommitMessage
     *
     * @return void
     */
    protected function commit(InputInterface $input, OutputInterface $output, string $resolvedCommitMessage): void
    {
        $question = new ConfirmationQuestion(sprintf(
            'Should I commit with message "<info>%s</>" for you in "<info>%s</>"?  [<fg=yellow>yes</>|<fg=yellow>no</>] (<fg=green>enter: yes</>) ',
            $resolvedCommitMessage,
            getcwd()
        ));

        $shouldCreate = $this->getHelper('question')->ask($input, $output, $question);

        if ($shouldCreate) {
            $this->commitChanges($input, $resolvedCommitMessage);
            $output->writeln(sprintf('Changes "<info>%s</>" committed.', $resolvedCommitMessage));
        } else {
            $output->writeln(sprintf('Changes "<info>%s</>" NOT committed.', $resolvedCommitMessage));
        }
    }

    /**
     * @codeCoverageIgnore
     *
     * @param InputInterface $input
     * @param string $resolvedCommitMessage
     *
     * @return void
     */
    protected function commitChanges(InputInterface $input,string $resolvedCommitMessage): void
    {
        $command = ['git', 'commit', '-m', $resolvedCommitMessage];

        if ($input->getOption(static::OPTION_COMMIT_ALL)) {
            $command[] = '-a';
        }

        $process = new Process($command, (string)getcwd());
        $process->run();
    }
}
