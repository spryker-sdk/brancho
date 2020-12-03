<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Brancho\Resolver;

use Brancho\Command\BranchBuilderCommand;
use Brancho\Context\ContextInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class JiraResolver extends AbstractResolver implements ConfigurableResolverInterface
{
    /**
     * @var string[]
     */
    protected $issueTypeMap = [
        'epic' => 'feature',
        'task' => 'feature',
        'bug' => 'bugfix',
    ];

    /**
     * @var string[]
     */
    protected $issueTypeToPrefixMap = [
        'epic' => 'master',
        'bug' => 'master',
        'story' => 'dev',
        'task' => 'dev',
    ];

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Brancho\Context\ContextInterface $context
     *
     * @return array|null
     */
    public function resolve(InputInterface $input, OutputInterface $output, ContextInterface $context): ?array
    {
        $issue = $this->getIssue($input, $output);

        $filter = $context->getFilter();
        $config = $context->getConfig()['jira'];

        $jiraIssue = $this->getFactory()->createJira()->getJiraIssue($issue, $config);

        if (isset($jiraIssue['errorMessages'])) {
            foreach ($jiraIssue['errorMessages'] as $errorMessage) {
                $output->writeln(sprintf('<fg=red>%s</>', $errorMessage));
            }

            return null;
        }

        $issue = $filter->filter($issue);
        $issueType = $filter->filter($jiraIssue['fields']['issuetype']['name']);
        $summary = $filter->filter($jiraIssue['fields']['summary']);

        if ($issueType === 'bug') {
            return (array)$this->createBugfixBranchName($issue, $summary);
        }

        if ($issueType === 'epic') {
            return $this->createEpicBranchNames($input, $output, $issue, $summary);
        }

        $epicOrStoryJiraIssue = $this->getParentJiraIssue($jiraIssue, $config);
        $epicOrStoryIssue = $filter->filter($epicOrStoryJiraIssue['key']);
        $issue = sprintf('%s/%s', $epicOrStoryIssue, $issue);

        if ($issueType === 'sub-task') {
            $epicJiraIssue = $this->getParentJiraIssue($epicOrStoryJiraIssue, $config);
            $epicIssue = $filter->filter($epicJiraIssue['key']);

            $issue = sprintf('%s/%s', $epicIssue, $issue);
        }

        return (array)$this->createBranchName($issue, $summary);
    }

    /**
     * @param string $issue
     * @param string $summary
     *
     * @return string
     */
    protected function createBugfixBranchName(string $issue, string $summary): string
    {
        return sprintf(
            'bugfix/%s-%s',
            $issue,
            $summary,
        );
    }

    /**
     * @group single
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string $issue
     * @param string $summary
     *
     * @return array
     */
    protected function createEpicBranchNames(InputInterface $input, OutputInterface $output, string $issue, string $summary): array
    {
        $branchNames = [
            sprintf('feature/%s/master-%s', $issue, $summary),
        ];

        $question = new ConfirmationQuestion('Should I also create an epic dev branch? [<fg=yellow>yes</>|<fg=yellow>no</>] (<fg=green>enter: yes</>) ');
        $helper = new QuestionHelper();

        $epicDevShouldBeCreated = $helper->ask($input, $output, $question);

        if ($epicDevShouldBeCreated) {
            $branchNames[] = sprintf('feature/%s/dev-%s', $issue, $summary);
        }

        return $branchNames;
    }

    /**
     * @param string $issue
     * @param string $summary
     *
     * @return string
     */
    protected function createBranchName(string $issue, string $summary): string
    {
        return sprintf(
            'feature/%s-%s',
            $issue,
            $summary,
        );
    }

    /**
     * @param \Brancho\Context\ContextInterface $context
     *
     * @return bool
     */
    public function isConfigured(ContextInterface $context): bool
    {
        return isset($context->getConfig()['jira']);
    }

    /**
     * @return array
     */
    public function getConfigurationInformation(): array
    {
        return [
            'jira' => [
                'host' => [
                    'question' => 'Please enter the host of your Jira e.g. https://spryker.atlassian.net',
                    'default' => 'https://spryker.atlassian.net',
                ],
                'username' => 'Please enter your Jira username',
                'password' => 'Please enter you Jira API key, you can get one here https://id.atlassian.com/manage-profile/security/api-tokens',
            ],
        ];
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return string
     */
    protected function getIssue(InputInterface $input, OutputInterface $output): string
    {
        /** @var string $issue */
        $issue = $input->getArgument(BranchBuilderCommand::ARGUMENT_ISSUE);

        if ($issue) {
            return (string)$issue;
        }

        $question = new Question('Please enter the Jira issue number e.g. "rk-123": ');
        $helper = new QuestionHelper();

        $issue = $helper->ask($input, $output, $question);

        if (!$issue) {
            $output->writeln('<fg=red>You need to enter a valid issue number.</>');

            return $this->getIssue($input, $output);
        }

        return $issue;
    }

    /**
     * @param array $jiraIssue
     * @param array $config
     *
     * @return array
     */
    protected function getParentJiraIssue(array $jiraIssue, array $config): array
    {
        return $this->getFactory()
            ->createJira()
            ->getJiraIssue($this->getParentIssue($jiraIssue), $config);
    }

    /**
     * @param array $jiraIssue
     *
     * @return string
     */
    protected function getParentIssue(array $jiraIssue): string
    {
        if (isset($jiraIssue['fields']['customfield_10008'])) {
            return $jiraIssue['fields']['customfield_10008'];
        }

        return $jiraIssue['fields']['parent']['key'];
    }
}
