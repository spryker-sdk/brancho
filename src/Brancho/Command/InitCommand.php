<?php

namespace Brancho\Command;

use Brancho\Config\Config;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

class InitCommand extends AbstractCommand
{
    public const CONFIG = 'config';
    public const CONFIG_SHORTCUT = 'c';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('init')
            ->setDescription('Initialize brancho configuration.')
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
        $questionHelper = $this->getHelper('question');

        $homeDirectoryConfigPath = $this->getHomeDirectory() . '/brancho/.brancho.local';

        if (file_exists($homeDirectoryConfigPath)) {
            $question = new ConfirmationQuestion(sprintf(
                'I found a "%s" when you continue, this will be re-written, should I continue? [<fg=yellow>yes</>] ',
                $homeDirectoryConfigPath
            ));

            $shouldContinue = $questionHelper->ask($input, $output, $question);
            if ($shouldContinue) {
                $this->configureBrancho($input, $output, $homeDirectoryConfigPath);

                return static::CODE_SUCCESS;
            }

            $output->writeln('Aborted creation of brancho local configuration.');

            return static::CODE_SUCCESS;
        }

        $this->configureBrancho($input, $output, $homeDirectoryConfigPath);

        return static::CODE_SUCCESS;
    }

    /**
     * @codeCoverageIgnore Path is mocked for testing
     *
     * @return string
     */
    protected function getHomeDirectory(): string
    {
        return (string)getenv('HOME');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $pathToLocalConfig
     *
     * @return void
     */
    protected function configureBrancho(InputInterface $input, OutputInterface $output, string $pathToLocalConfig): void
    {
        $config = $this->loadConfig($this->getConfigPath($input));
        $resolvers = $config[Config::RESOLVERS];

        $localConfiguration = [];

        if (isset($resolvers['jira'])) {
            $localConfiguration = $this->configureJira($input, $output, $localConfiguration);
        }

        if (count($localConfiguration) > 0) {
            if (!is_dir(dirname($pathToLocalConfig))) {
                mkdir(dirname($pathToLocalConfig), 0777, true);
            }

            $ymlConfiguration = Yaml::dump($localConfiguration);
            file_put_contents($pathToLocalConfig, $ymlConfiguration);

            $output->writeln(sprintf('Added configuration to <fg=yellow>%s</>', $pathToLocalConfig));

            return;
        }

        $output->writeln('No resolver which needs configuration found, configuration not changed.');
    }

    /**
     * @param InputInterface $input
     *
     * @return string
     */
    protected function getConfigPath(InputInterface $input): string
    {
        /** @var string $configPath */
        $configPath = $input->getOption(BranchBuilderCommand::CONFIG);

        return $configPath;
    }

    /**
     * @param string $pathToConfig
     *
     * @return array
     */
    protected function loadConfig(string $pathToConfig): array
    {
        return $this->getFactory()->createConfig()->load($pathToConfig);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param array $localConfiguration
     *
     * @return array
     */
    protected function configureJira(InputInterface $input, OutputInterface $output, array $localConfiguration): array
    {
        $questionHelper = $this->getHelper('question');

        $question = new Question('Please enter your JIRA host: ');
        $localConfiguration['jira']['host'] = $questionHelper->ask($input, $output, $question);

        $question = new Question('Please enter your JIRA username: ');
        $localConfiguration['jira']['username'] = $questionHelper->ask($input, $output, $question);

        $question = new Question('Please enter your JIRA access-token (<fg=yellow>https://id.atlassian.com/manage-profile/security/api-tokens</>): ');
        $localConfiguration['jira']['password'] = $questionHelper->ask($input, $output, $question);

        return $localConfiguration;
    }
}
