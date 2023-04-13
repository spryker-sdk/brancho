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
     * @var array<string>
     */
    protected $issueTypeMap = [
        'epic' => 'feature',
        'task' => 'feature',
        'bug' => 'bugfix',
    ];

    /**
     * @var array<string>
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
        $issueKey = $this->getIssue($input, $output);

        $filter = $context->getFilter();
        $config = $context->getConfig()['jira'];

        $jiraIssueData = $this->getFactory()->createJira()->getJiraIssue($issueKey, $config);

        if (isset($jiraIssueData['errorMessages'])) {
            foreach ($jiraIssueData['errorMessages'] as $errorMessage) {
                $output->writeln(sprintf('<fg=red>%s</>', $errorMessage));
            }

            return null;
        }

        $issueKey = $filter->filter($issueKey);
        $issueType = $filter->filter($jiraIssueData['fields']['issuetype']['name']);
        $issueSummary = $filter->filter($jiraIssueData['fields']['summary']);

        if ($issueType === 'bug') {
            return (array)$this->createBugfixBranchName($issueKey, $issueSummary);
        }

        if ($issueType === 'epic') {
            return $this->createEpicBranchNames($input, $output, $issueKey, $issueSummary);
        }

        $parentIssueData = $this->getParentJiraIssue($jiraIssueData, $config);

        if (!$parentIssueData) {
            $output->writeln('<comment>Warning: Ticket has no parent or epic branch.</>');
        } else {
            $parentIssueSlug = $filter->filter($parentIssueData['key']);
            $parentIssueType = $filter->filter($parentIssueData['fields']['issuetype']['name']);

            $issueKey = sprintf('%s/%s', $parentIssueSlug, $issueKey);

            if ($issueType === 'sub-task' && $parentIssueType !== 'epic') {
                $epicJiraIssue = $this->getParentJiraIssue($parentIssueData, $config);

                if ($epicJiraIssue) {
                    $epicIssue = $filter->filter($epicJiraIssue['key']);
                    $issueKey = sprintf('%s/%s', $epicIssue, $issueKey);
                }
            }
        }

        return [
            $this->createBranchName($issueKey, $issueSummary),
        ];
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
     * @return array|null
     */
    protected function getParentJiraIssue(array $jiraIssue, array $config): ?array
    {
        $parentIssue = $this->getParentIssue($jiraIssue);

        if (!$parentIssue) {
            return null;
        }

        return $this->getFactory()
            ->createJira()
            ->getJiraIssue($parentIssue, $config);
    }

    /**
     * @param array $jiraIssue
     *
     * @return string|null
     */
    protected function getParentIssue(array $jiraIssue): ?string
    {
        return $jiraIssue['fields']['customfield_10008'] ?? $jiraIssue['fields']['parent']['key'] ?? null;
    }
}
