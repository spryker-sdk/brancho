<?php

namespace Brancho\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Process;

class BranchBuilderCommand extends AbstractCommand
{
    public const CONFIG = 'config';
    public const CONFIG_SHORTCUT = 'c';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('branch')
            ->setDescription('Builds valid branch names.')
            ->addOption(
                static::CONFIG,
                static::CONFIG_SHORTCUT,
                InputOption::VALUE_REQUIRED,
                'Path to a configuration file (default: .brancho)',
                getcwd() . '/.brancho'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $brancho = $this->getFactory()->createBrancho();
        $resolvedBranchName = $brancho->resolveBranchName($input, $output);

        if (!$resolvedBranchName) {
            $output->writeln('<fg=red>The resolved branch name is empty some thing went wrong.</>');

            return static::CODE_ERROR;
        }

        $question = new ConfirmationQuestion(sprintf(
            'Should I create the branch "<info>%s</>" for you in "<info>%s</>"? ',
            $resolvedBranchName,
            getcwd()
        ));

        $shouldCreate = $this->getHelper('question')->ask($input, $output, $question);

        if ($shouldCreate) {
            $process = new Process(['git', 'checkout', '-b', $resolvedBranchName]);
            $process->run();

            $output->writeln(sprintf('Branch "<info>%s</>" created.', $resolvedBranchName));

            return static::CODE_SUCCESS;
        }

        $output->writeln(sprintf('Branch "<info>%s</>" NOT created.', $resolvedBranchName));

        return static::CODE_SUCCESS;
    }
}
