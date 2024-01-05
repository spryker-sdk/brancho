<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Brancho\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Process;

class BranchBuilderCommand extends AbstractCommand
{
    /**
     * @var string
     */
    public const ARGUMENT_ISSUE = 'issue';

    /**
     * @var string
     */
    public const OPTION_CONFIG = 'config';

    /**
     * @var string
     */
    public const OPTION_CONFIG_SHORTCUT = 'c';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('branch')
            ->setDescription('Builds branch names.')
            ->addArgument(static::ARGUMENT_ISSUE, InputArgument::OPTIONAL, 'Issue number a branch should be created for e.g. "rk-123".')
            ->addOption(
                static::OPTION_CONFIG,
                static::OPTION_CONFIG_SHORTCUT,
                InputOption::VALUE_REQUIRED,
                'Path to a configuration file (default: .brancho)',
                ROOT_DIR . '/.brancho',
            );
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
        $resolvedBranchNames = $brancho->resolveBranchNames($input, $output);

        if (!$resolvedBranchNames) {
            $output->writeln('<fg=red>No branches to create.</>');

            return static::CODE_ERROR;
        }

        $this->createBranches($input, $output, $resolvedBranchNames);

        return static::CODE_SUCCESS;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param array $resolvedBranchNames
     *
     * @return void
     */
    protected function createBranches(InputInterface $input, OutputInterface $output, array $resolvedBranchNames): void
    {
        foreach ($resolvedBranchNames as $resolvedBranchName) {
            $question = new ConfirmationQuestion(sprintf(
                'Should I create the branch "<info>%s</>" for you in "<info>%s</>"?  [<fg=yellow>yes</>|<fg=yellow>no</>] (<fg=green>enter: yes</>) ',
                $resolvedBranchName,
                getcwd(),
            ));
            /** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */
            $helper = $this->getHelper('question');
            $shouldCreate = $helper->ask($input, $output, $question);

            if ($shouldCreate) {
                $this->createBranch($resolvedBranchName);
                $output->writeln(sprintf('Branch "<info>%s</>" created.', $resolvedBranchName));
            } else {
                $output->writeln(sprintf('Branch "<info>%s</>" NOT created.', $resolvedBranchName));
            }
        }
    }

    /**
     * @codeCoverageIgnore
     *
     * @param string $branchName
     *
     * @return void
     */
    protected function createBranch(string $branchName): void
    {
        $process = new Process(['git', 'checkout', '-b', $branchName], (string)getcwd());
        $process->run();
    }
}
