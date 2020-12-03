<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace BranchoTest\BranchoTest\Command;

use Brancho\BranchoFactory;
use Brancho\Command\BranchBuilderCommand;
use Brancho\Jira\Jira;
use Brancho\Resolver\ResolverDecorator;
use Codeception\Stub;
use Codeception\Test\Unit;
use org\bovigo\vfs\vfsStream;

class BranchBuilderCommandTest extends Unit
{
    /**
     * @var \BranchoTest\CommandTester
     */
    protected $tester;

    /**
     * Tests that a slugified branch name will be created from the given user input.
     *
     * @return void
     */
    public function testBranchCreationWithTheDescriptionResolver(): void
    {
        // Arrange
        $branchBuilderCommandMock = $this->createBranchBuilderCommandMock();

        $commandTester = $this->tester->getConsoleTester($branchBuilderCommandMock);
        $commandTester->setInputs(['Branch description']);

        // Act
        $commandTester->execute(['--config' => codecept_data_dir('pattern-description.yml')]);

        // Assert
        $this->assertStringContainsString('branch-description', $commandTester->getDisplay());
        $this->assertStringContainsString('"branch-description" created.', $commandTester->getDisplay());
    }

    /**
     * @return \Brancho\Command\BranchBuilderCommand
     */
    protected function createBranchBuilderCommandMock(): BranchBuilderCommand
    {
        /** @var \Brancho\Command\BranchBuilderCommand $branchBuilderCommandMock */
        $branchBuilderCommandMock = Stub::construct(BranchBuilderCommand::class, [], [
            'createBranch' => function () {
            },
        ]);

        return $branchBuilderCommandMock;
    }

    /**
     * Tests that a correct bug fix branch name is created.
     *
     * @example bugfix/rk-123-ticket-summary
     *
     * @return void
     */
    public function testJiraBugIssueBranchNameCreation(): void
    {
        // Arrange
        $jiraMock = Stub::make(Jira::class, [
            'getJiraIssue' => function () {
                return include codecept_data_dir('jira-bug-response.php');
            },
        ]);

        /** @var \Brancho\BranchoFactory $factoryMock */
        $factoryMock = Stub::make(BranchoFactory::class, [
            'createJira' => function () use ($jiraMock) {
                return $jiraMock;
            },
        ]);

        $branchBuilderCommandMock = $this->createBranchBuilderCommandMock();
        $branchBuilderCommandMock->setFactory($factoryMock);

        $commandTester = $this->tester->getConsoleTester($branchBuilderCommandMock);
        $commandTester->setInputs(['rk-123']);

        // Act
        $commandTester->execute(['--config' => codecept_data_dir('pattern-jira.yml')]);

        // Assert
        $this->assertStringContainsString('bugfix/rk-123-ticket-summary', $commandTester->getDisplay());
        $this->assertStringContainsString('"bugfix/rk-123-ticket-summary" created.', $commandTester->getDisplay());
    }

    /**
     * Tests that a correct bug fix branch name is created.
     *
     * @example bugfix/rk-123-ticket-summary
     *
     * @return void
     */
    public function testJiraIssueMustBeAValidIssue(): void
    {
        // Arrange
        $jiraMock = Stub::make(Jira::class, [
            'getJiraIssue' => function () {
                return include codecept_data_dir('jira-bug-response.php');
            },
        ]);

        /** @var \Brancho\BranchoFactory $factoryMock */
        $factoryMock = Stub::make(BranchoFactory::class, [
            'createJira' => function () use ($jiraMock) {
                return $jiraMock;
            },
        ]);

        $branchBuilderCommandMock = $this->createBranchBuilderCommandMock();
        $branchBuilderCommandMock->setFactory($factoryMock);

        $commandTester = $this->tester->getConsoleTester($branchBuilderCommandMock);
        $commandTester->setInputs(['', 'rk-123']); // first input is invalid

        // Act
        $commandTester->execute(['--config' => codecept_data_dir('pattern-jira.yml')]);

        // Assert
        $this->assertStringContainsString('You need to enter a valid issue number.', $commandTester->getDisplay());
        $this->assertStringContainsString('bugfix/rk-123-ticket-summary', $commandTester->getDisplay());
        $this->assertStringContainsString('"bugfix/rk-123-ticket-summary" created.', $commandTester->getDisplay());
    }

    /**
     * Tests that command can be executed with an issue argument `vendor/bin/brancho branch rk-123` rather than asking the user for input.
     *
     * @return void
     */
    public function testJiraBugIssueBranchNameCreationWithIssueArgument(): void
    {
        // Arrange
        $jiraMock = Stub::make(Jira::class, [
            'getJiraIssue' => function () {
                return include codecept_data_dir('jira-bug-response.php');
            },
        ]);

        /** @var \Brancho\BranchoFactory $factoryMock */
        $factoryMock = Stub::make(BranchoFactory::class, [
            'createJira' => function () use ($jiraMock) {
                return $jiraMock;
            },
        ]);

        $branchBuilderCommandMock = $this->createBranchBuilderCommandMock();
        $branchBuilderCommandMock->setFactory($factoryMock);

        $commandTester = $this->tester->getConsoleTester($branchBuilderCommandMock);

        // Act
        $commandTester->execute(['issue' => 'rk-123', '--config' => codecept_data_dir('pattern-jira.yml')]);

        // Assert
        $this->assertStringContainsString('bugfix/rk-123-ticket-summary', $commandTester->getDisplay());
        $this->assertStringContainsString('"bugfix/rk-123-ticket-summary" created.', $commandTester->getDisplay());
    }

    /**
     * @return void
     */
    public function testJiraEpicMasterIssueBranchNameCreation(): void
    {
        // Arrange
        $jiraMock = Stub::make(Jira::class, [
            'getJiraIssue' => function () {
                return include codecept_data_dir('jira-epic-response.php');
            },
        ]);

        /** @var \Brancho\BranchoFactory $factoryMock */
        $factoryMock = Stub::make(BranchoFactory::class, [
            'createJira' => function () use ($jiraMock) {
                return $jiraMock;
            },
        ]);

        $branchBuilderCommandMock = $this->createBranchBuilderCommandMock();
        $branchBuilderCommandMock->setFactory($factoryMock);

        $commandTester = $this->tester->getConsoleTester($branchBuilderCommandMock);

        // Act
        $commandTester->execute(['issue' => 'rk-123', '--config' => codecept_data_dir('pattern-jira.yml')]);

        // Assert
        $this->assertStringContainsString('feature/rk-123/master-epic-summary', $commandTester->getDisplay());
        $this->assertStringContainsString('"feature/rk-123/master-epic-summary" created.', $commandTester->getDisplay());
    }

    /**
     * @return void
     */
    public function testJiraEpicDevIssueBranchShouldBeCreatedIfRequired(): void
    {
        // Arrange
        $jiraMock = Stub::make(Jira::class, [
            'getJiraIssue' => function () {
                return include codecept_data_dir('jira-epic-response.php');
            },
        ]);

        /** @var \Brancho\BranchoFactory $factoryMock */
        $factoryMock = Stub::make(BranchoFactory::class, [
            'createJira' => function () use ($jiraMock) {
                return $jiraMock;
            },
        ]);

        $branchBuilderCommandMock = $this->createBranchBuilderCommandMock();
        $branchBuilderCommandMock->setFactory($factoryMock);

        $commandTester = $this->tester->getConsoleTester($branchBuilderCommandMock);

        // Act
        $commandTester->setInputs(['y']);
        $commandTester->execute(['issue' => 'rk-123', '--config' => codecept_data_dir('pattern-jira.yml')]);

        // Assert
        $this->assertStringContainsString('feature/rk-123/master-epic-summary', $commandTester->getDisplay());
        $this->assertStringContainsString('"feature/rk-123/master-epic-summary" created.', $commandTester->getDisplay());

        // Assert
        $this->assertStringContainsString('feature/rk-123/dev-epic-summary', $commandTester->getDisplay());
        $this->assertStringContainsString('"feature/rk-123/dev-epic-summary" created.', $commandTester->getDisplay());
    }

    /**
     * @return void
     */
    public function testJiraEpicDevIssueBranchShouldNotBeCreatedIfNotRequired(): void
    {
        // Arrange
        $jiraMock = Stub::make(Jira::class, [
            'getJiraIssue' => function () {
                return include codecept_data_dir('jira-epic-response.php');
            },
        ]);

        /** @var \Brancho\BranchoFactory $factoryMock */
        $factoryMock = Stub::make(BranchoFactory::class, [
            'createJira' => function () use ($jiraMock) {
                return $jiraMock;
            },
        ]);

        $branchBuilderCommandMock = $this->createBranchBuilderCommandMock();
        $branchBuilderCommandMock->setFactory($factoryMock);

        $commandTester = $this->tester->getConsoleTester($branchBuilderCommandMock);

        // Act
        $commandTester->setInputs(['n']);
        $commandTester->execute(['issue' => 'rk-123', '--config' => codecept_data_dir('pattern-jira.yml')]);

        // Assert
        $this->assertStringContainsString('feature/rk-123/master-epic-summary', $commandTester->getDisplay());
        $this->assertStringContainsString('"feature/rk-123/master-epic-summary" created.', $commandTester->getDisplay());

        // Assert
        $this->assertStringNotContainsString('feature/rk-123/dev-epic-summary', $commandTester->getDisplay());
        $this->assertStringNotContainsString('"feature/rk-123/dev-epic-summary" created.', $commandTester->getDisplay());
    }

    /**
     * Story: Tests branch name consists of two tickets, the story and the parent epic.
     *
     * @return void
     */
    public function testJiraStoryIssueBranchNameCreation(): void
    {
        // Arrange
        $storyResponse = include codecept_data_dir('jira-story-response.php');
        $epicResponse = include codecept_data_dir('jira-epic-response.php');

        $jiraMock = Stub::make(Jira::class, [
            'getJiraIssue' => Stub::consecutive($storyResponse, $epicResponse),
        ]);

        /** @var \Brancho\BranchoFactory $factoryMock */
        $factoryMock = Stub::make(BranchoFactory::class, [
            'createJira' => function () use ($jiraMock) {
                return $jiraMock;
            },
        ]);

        $branchBuilderCommandMock = $this->createBranchBuilderCommandMock();
        $branchBuilderCommandMock->setFactory($factoryMock);

        $commandTester = $this->tester->getConsoleTester($branchBuilderCommandMock);

        // Act
        $commandTester->execute(['issue' => 'rk-231', '--config' => codecept_data_dir('pattern-jira.yml')]);

        // Assert
        $this->assertStringContainsString('"feature/rk-123/rk-231-story-summary" created.', $commandTester->getDisplay());
    }

    /**
     * Task: Tests branch name consists of two tickets, the task and the parent epic.
     *
     * @return void
     */
    public function testJiraTaskIssueBranchNameCreation(): void
    {
        // Arrange
        $taskResponse = include codecept_data_dir('jira-task-response.php');
        $epicResponse = include codecept_data_dir('jira-epic-response.php');

        $jiraMock = Stub::make(Jira::class, [
            'getJiraIssue' => Stub::consecutive($taskResponse, $epicResponse),
        ]);

        /** @var \Brancho\BranchoFactory $factoryMock */
        $factoryMock = Stub::make(BranchoFactory::class, [
            'createJira' => function () use ($jiraMock) {
                return $jiraMock;
            },
        ]);

        $branchBuilderCommandMock = $this->createBranchBuilderCommandMock();
        $branchBuilderCommandMock->setFactory($factoryMock);

        $commandTester = $this->tester->getConsoleTester($branchBuilderCommandMock);

        // Act
        $commandTester->execute(['issue' => 'rk-321', '--config' => codecept_data_dir('pattern-jira.yml')]);

        // Assert
        $this->assertStringContainsString('"feature/rk-123/rk-321-task-summary" created.', $commandTester->getDisplay());
    }

    /**
     * @group single
     *
     * @return void
     */
    public function testJiraSubTaskIssueBranchNameCreation(): void
    {
        // Arrange
        $subTaskResponse = include codecept_data_dir('jira-sub-task-response.php');
        $taskResponse = include codecept_data_dir('jira-task-response.php');
        $epicResponse = include codecept_data_dir('jira-epic-response.php');

        $jiraMock = Stub::make(Jira::class, [
            'getJiraIssue' => Stub::consecutive($subTaskResponse, $taskResponse, $epicResponse),
        ]);

        /** @var \Brancho\BranchoFactory $factoryMock */
        $factoryMock = Stub::make(BranchoFactory::class, [
            'createJira' => function () use ($jiraMock) {
                return $jiraMock;
            },
        ]);

        $branchBuilderCommandMock = $this->createBranchBuilderCommandMock();
        $branchBuilderCommandMock->setFactory($factoryMock);

        $commandTester = $this->tester->getConsoleTester($branchBuilderCommandMock);

        // Act
        $commandTester->execute(['issue' => 'rk-456', '--config' => codecept_data_dir('pattern-jira.yml')]);

        // Assert
        $this->assertStringContainsString('"feature/rk-123/rk-321/rk-456-sub-task-summary" created.', $commandTester->getDisplay());
    }

    /**
     * @return void
     */
    public function testJiraWillAskForConfigurationWhenConfigDoesNotExist(): void
    {
        // Arrange
        $taskResponse = include codecept_data_dir('jira-task-response.php');
        $epicResponse = include codecept_data_dir('jira-epic-response.php');

        $rootDirectoryMock = vfsStream::setup();

        $jiraMock = Stub::make(Jira::class, [
            'getJiraIssue' => Stub::consecutive($taskResponse, $epicResponse),
        ]);

        $resolverDecoratorMock = Stub::make(ResolverDecorator::class, [
            'getRootDirectory' => $rootDirectoryMock->url(),
        ]);

        /** @var \Brancho\BranchoFactory $factoryMock */
        $factoryMock = Stub::make(BranchoFactory::class, [
            'createJira' => $jiraMock,
            'createResolverDecorator' => $resolverDecoratorMock,
        ]);

        $branchBuilderCommandMock = $this->createBranchBuilderCommandMock();
        $branchBuilderCommandMock->setFactory($factoryMock);

        $commandTester = $this->tester->getConsoleTester($branchBuilderCommandMock);
        $commandTester->setInputs(['https://spryker.atlassian.net', 'Spryker', 'api-key']);

        // Act
        $commandTester->execute(['issue' => 'rk-321', '--config' => codecept_data_dir('pattern-jira-without-config.yml')]);

        // Assert
        $this->assertStringContainsString('Please enter the host of your Jira e.g. https://spryker.atlassian.net', $commandTester->getDisplay());
        $this->assertStringContainsString('Please enter your Jira username', $commandTester->getDisplay());
        $this->assertStringContainsString('Please enter you Jira API key, you can get one here https://id.atlassian.com/manage-profile/security/api-tokens', $commandTester->getDisplay());
        $this->assertStringContainsString('"feature/rk-123/rk-321-task-summary" created.', $commandTester->getDisplay());

        $this->assertTrue($rootDirectoryMock->hasChild('.brancho.local'));
    }

    /**
     * @return void
     */
    public function testJiraWillAskForConfigurationAndAppendsItToAnExistingLocalConfiguration(): void
    {
        // Arrange
        $taskResponse = include codecept_data_dir('jira-task-response.php');
        $epicResponse = include codecept_data_dir('jira-epic-response.php');

        $rootDirectoryMock = vfsStream::setup();

        $jiraMock = Stub::make(Jira::class, [
            'getJiraIssue' => Stub::consecutive($taskResponse, $epicResponse),
        ]);

        $resolverDecoratorMock = Stub::make(ResolverDecorator::class, [
            'getRootDirectory' => $rootDirectoryMock->url(),
        ]);

        // Add an existing config file where we want to add the new config to.
        $mockedBranchoLocalPath = $rootDirectoryMock->url() . '/.brancho.local';
        file_put_contents($mockedBranchoLocalPath, 'key: value');

        /** @var \Brancho\BranchoFactory $factoryMock */
        $factoryMock = Stub::make(BranchoFactory::class, [
            'createJira' => $jiraMock,
            'createResolverDecorator' => $resolverDecoratorMock,
        ]);

        $branchBuilderCommandMock = $this->createBranchBuilderCommandMock();
        $branchBuilderCommandMock->setFactory($factoryMock);

        $commandTester = $this->tester->getConsoleTester($branchBuilderCommandMock);
        $commandTester->setInputs(['https://spryker.atlassian.net', 'Spryker', 'api-key']);

        // Act
        $commandTester->execute(['issue' => 'rk-321', '--config' => codecept_data_dir('pattern-jira-without-config.yml')]);

        // Assert
        $this->assertStringContainsString('Please enter the host of your Jira e.g. https://spryker.atlassian.net', $commandTester->getDisplay());
        $this->assertStringContainsString('Please enter your Jira username', $commandTester->getDisplay());
        $this->assertStringContainsString('Please enter you Jira API key, you can get one here https://id.atlassian.com/manage-profile/security/api-tokens', $commandTester->getDisplay());
        $this->assertStringContainsString('"feature/rk-123/rk-321-task-summary" created.', $commandTester->getDisplay());

        $this->assertTrue($rootDirectoryMock->hasChild('.brancho.local'));
    }

    /**
     * @return void
     */
    public function testJiraOutputsErrorMessageInCaseOfError(): void
    {
        // Arrange
        $jiraMock = Stub::make(Jira::class, [
            'getJiraIssue' => function () {
                return include codecept_data_dir('jira-error-response.php');
            },
        ]);

        /** @var \Brancho\BranchoFactory $factoryMock */
        $factoryMock = Stub::make(BranchoFactory::class, [
            'createJira' => function () use ($jiraMock) {
                return $jiraMock;
            },
        ]);

        $branchBuilderCommandMock = $this->createBranchBuilderCommandMock();
        $branchBuilderCommandMock->setFactory($factoryMock);

        $commandTester = $this->tester->getConsoleTester($branchBuilderCommandMock);
        $commandTester->setInputs(['rk-123']);

        // Act
        $commandTester->execute(['--config' => codecept_data_dir('pattern-jira.yml')]);

        // Assert
        $this->assertStringContainsString(
            'Issue does not exist or you do not have permission to see it.',
            $commandTester->getDisplay()
        );
    }

    /**
     * @return void
     */
    public function testExecuteOnlyShowsBranchNameWhenUserAnswerWithNoForCreation(): void
    {
        // Arrange
        $branchBuilderCommandMock = $this->createBranchBuilderCommandMock();

        $commandTester = $this->tester->getConsoleTester($branchBuilderCommandMock);
        $commandTester->setInputs(['Branch description', 'n']);

        // Act
        $commandTester->execute(['--config' => codecept_data_dir('pattern-description.yml')]);

        // Assert
        $this->assertStringContainsString('branch-description', $commandTester->getDisplay());
        $this->assertStringContainsString('"branch-description" NOT created.', $commandTester->getDisplay());
    }
}
