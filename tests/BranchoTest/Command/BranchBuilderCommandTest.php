<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace BranchoTest\Command;

use Brancho\BranchoFactory;
use Brancho\Command\BranchBuilderCommand;
use Brancho\Jira\Jira;
use Brancho\Resolver\ResolverDecorator;
use Codeception\Stub;
use Codeception\Test\Unit;
use org\bovigo\vfs\vfsStream;

/**
 * @group Brancho
 * @group Command
 * @group BranchBuilderCommandTest
 */
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
        $branchBuilderCommandMock = $this->tester->createBranchBuilderCommandMock();

        $commandTester = $this->tester->getConsoleTester($branchBuilderCommandMock);
        $commandTester->setInputs(['Branch description']);

        // Act
        $commandTester->execute(['--config' => codecept_data_dir('pattern-description.yml')]);

        // Assert
        $this->assertStringContainsString('branch-description', $commandTester->getDisplay());
        $this->assertStringContainsString('"branch-description" created.', $commandTester->getDisplay());
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
        $branchBuilderCommandMock = $this->tester->haveBranchBuilderCommandMock([
            'jira-bug-response.php'
        ]);

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
        $branchBuilderCommandMock = $this->tester->haveBranchBuilderCommandMock([
            'jira-bug-response.php'
        ]);

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
        $branchBuilderCommandMock = $this->tester->haveBranchBuilderCommandMock([
            'jira-bug-response.php'
        ]);

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
        $branchBuilderCommandMock = $this->tester->haveBranchBuilderCommandMock([
            'jira-epic-response.php'
        ]);

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
        $branchBuilderCommandMock = $this->tester->haveBranchBuilderCommandMock([
            'jira-epic-response.php'
        ]);

        $commandTester = $this->tester->getConsoleTester($branchBuilderCommandMock);

        // Act
        $commandTester->setInputs(['y']);
        $commandTester->execute(['issue' => 'rk-123', '--config' => codecept_data_dir('pattern-jira.yml')]);

        // Assert
        $this->assertStringContainsString('feature/rk-123/master-epic-summary', $commandTester->getDisplay());
        $this->assertStringContainsString('"feature/rk-123/master-epic-summary" created.', $commandTester->getDisplay());
    }

    /**
     * @return void
     */
    public function testJiraEpicDevIssueBranchShouldNotBeCreatedIfNotRequired(): void
    {
        // Arrange
        $branchBuilderCommandMock = $this->tester->haveBranchBuilderCommandMock([
            'jira-epic-response.php'
        ]);

        $commandTester = $this->tester->getConsoleTester($branchBuilderCommandMock);

        // Act
        $commandTester->setInputs(['n']);
        $commandTester->execute(['issue' => 'rk-123', '--config' => codecept_data_dir('pattern-jira.yml')]);

        // Assert
        $this->assertStringContainsString('feature/rk-123/master-epic-summary', $commandTester->getDisplay());
        $this->assertStringContainsString('"feature/rk-123/master-epic-summary" created.', $commandTester->getDisplay());

    }

    /**
     * Story: Tests branch name consists of two tickets, the story and the parent epic.
     *
     * @return void
     */
    public function testJiraStoryIssueBranchNameCreation(): void
    {
        // Arrange
        $branchBuilderCommandMock = $this->tester->haveBranchBuilderCommandMock([
            'jira-story-response.php',
            'jira-epic-response.php',
        ]);

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
        $branchBuilderCommandMock = $this->tester->haveBranchBuilderCommandMock([
            'jira-task-response.php',
            'jira-epic-response.php',
        ]);

        $commandTester = $this->tester->getConsoleTester($branchBuilderCommandMock);

        // Act
        $commandTester->execute(['issue' => 'rk-321', '--config' => codecept_data_dir('pattern-jira.yml')]);

        // Assert
        $this->assertStringContainsString('"feature/rk-123/rk-321-task-summary" created.', $commandTester->getDisplay());
    }

    /**
     * @return void
     */
    public function testJiraSubTaskIssueBranchNameCreation(): void
    {
        // Arrange
        $branchBuilderCommandMock = $this->tester->haveBranchBuilderCommandMock([
            'jira-sub-task-response.php',
            'jira-task-response.php',
            'jira-epic-response.php',
        ]);

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
        $rootDirectoryMock = vfsStream::setup();
        $branchBuilderCommandMock = $this->tester->haveBranchBuilderCommandMock(
            [
                'jira-task-response.php',
                'jira-epic-response.php',
            ],
            $rootDirectoryMock
        );

        $commandTester = $this->tester->getConsoleTester($branchBuilderCommandMock);
        $commandTester->setInputs(['https://spryker.atlassian.net', 'Spryker', 'api-key']);

        // Act
        $commandTester->execute(['issue' => 'rk-321', '--config' => codecept_data_dir('pattern-jira-without-config.yml')]);

        // Assert
        $this->tester->assertCommandAskedCredentials($commandTester);

        $this->assertStringContainsString('"feature/rk-123/rk-321-task-summary" created.', $commandTester->getDisplay());

        $this->assertTrue($rootDirectoryMock->hasChild('.brancho.local'));
    }

    /**
     * @return void
     */
    public function testJiraWillAskForContinueIfTaskDoesNotHaveParent(): void
    {
        // Arrange
        $rootDirectoryMock = vfsStream::setup();
        $branchBuilderCommandMock = $this->tester->haveBranchBuilderCommandMock(
            [
                'jira-task-response-without-epic.php',
            ],
            $rootDirectoryMock
        );

        $commandTester = $this->tester->getConsoleTester($branchBuilderCommandMock);
        $commandTester->setInputs(['https://spryker.atlassian.net', 'Spryker', 'api-key', 'yes']);

        // Act
        $commandTester->execute(['issue' => 'rk-321', '--config' => codecept_data_dir('pattern-jira-without-config.yml')]);

        // Assert
        $this->tester->assertCommandAskedCredentials($commandTester);

        $this->assertStringContainsString('Warning: Ticket has no parent or epic branch.', $commandTester->getDisplay());
        $this->assertStringContainsString('Should I create the branch "feature/rk-321-task-summary"', $commandTester->getDisplay());
        $this->assertStringContainsString('"feature/rk-321-task-summary" created.', $commandTester->getDisplay());

        $this->assertTrue($rootDirectoryMock->hasChild('.brancho.local'));
    }

    /**
     * @return void
     */
    public function testJiraShouldNotCreateBranchIfDoesNotHaveParentAndCreationNotAllowed(): void
    {
        // Arrange
        $rootDirectoryMock = vfsStream::setup();
        $branchBuilderCommandMock = $this->tester->haveBranchBuilderCommandMock(
            [
                'jira-task-response-without-epic.php',
            ],
            $rootDirectoryMock
        );

        $commandTester = $this->tester->getConsoleTester($branchBuilderCommandMock);
        $commandTester->setInputs(['https://spryker.atlassian.net', 'Spryker', 'api-key', 'no']);

        // Act
        $commandTester->execute(['issue' => 'rk-321', '--config' => codecept_data_dir('pattern-jira-without-config.yml')]);

        // Assert
        $this->tester->assertCommandAskedCredentials($commandTester);

        $this->assertStringContainsString('Warning: Ticket has no parent or epic branch.', $commandTester->getDisplay());
        $this->assertStringContainsString('Should I create the branch "feature/rk-321-task-summary"', $commandTester->getDisplay());
        $this->assertStringNotContainsString('"feature/rk-321/dev-task-summary" created.', $commandTester->getDisplay());

        $this->assertTrue($rootDirectoryMock->hasChild('.brancho.local'));
    }

    /**
     * @return void
     */
    public function testJiraWillAskForConfigurationAndAppendsItToAnExistingLocalConfiguration(): void
    {
        // Arrange
        $rootDirectoryMock = vfsStream::setup();
        $branchBuilderCommandMock = $this->tester->haveBranchBuilderCommandMock(
            [
                'jira-task-response.php',
                'jira-epic-response.php',
            ],
            $rootDirectoryMock
        );

        // Add an existing config file where we want to add the new config to.
        $mockedBranchoLocalPath = $rootDirectoryMock->url() . '/.brancho.local';
        file_put_contents($mockedBranchoLocalPath, 'key: value');

        $commandTester = $this->tester->getConsoleTester($branchBuilderCommandMock);
        $commandTester->setInputs(['https://spryker.atlassian.net', 'Spryker', 'api-key']);

        // Act
        $commandTester->execute(['issue' => 'rk-321', '--config' => codecept_data_dir('pattern-jira-without-config.yml')]);

        // Assert
        $this->tester->assertCommandAskedCredentials($commandTester);

        $this->assertStringContainsString('"feature/rk-123/rk-321-task-summary" created.', $commandTester->getDisplay());

        $this->assertTrue($rootDirectoryMock->hasChild('.brancho.local'));
    }

    /**
     * Tests when a sub-task's parent is an epic and not an expected task will not search for a task but will use the epic issue number.
     *
     * @return void
     */
    public function testJiraSubTaskWithEpicParentInsteadOfExpectedTaskParent(): void
    {
        // Arrange
        $rootDirectoryMock = vfsStream::setup();
        $branchBuilderCommandMock = $this->tester->haveBranchBuilderCommandMock(
            [
                'jira-sub-task-response.php',
                'jira-epic-response.php',
            ],
            $rootDirectoryMock
        );

        $commandTester = $this->tester->getConsoleTester($branchBuilderCommandMock);

        // Act
        $commandTester->execute(['issue' => 'rk-456', '--config' => codecept_data_dir('pattern-jira-without-config.yml')]);

        // Assert
        $this->assertStringContainsString('"feature/rk-123/rk-456-sub-task-summary" created.', $commandTester->getDisplay());

        $this->assertTrue($rootDirectoryMock->hasChild('.brancho.local'));
    }

    /**
     * @return void
     */
    public function testJiraOutputsErrorMessageInCaseOfError(): void
    {
        // Arrange
        $branchBuilderCommandMock = $this->tester->haveBranchBuilderCommandMock([
            'jira-error-response.php',
        ]);

        $commandTester = $this->tester->getConsoleTester($branchBuilderCommandMock);
        $commandTester->setInputs(['rk-123']);

        // Act
        $commandTester->execute(['--config' => codecept_data_dir('pattern-jira.yml')]);

        // Assert
        $this->assertStringContainsString(
            'Issue does not exist or you do not have permission to see it.',
            $commandTester->getDisplay(),
        );
    }

    /**
     * @return void
     */
    public function testExecuteOnlyShowsBranchNameWhenUserAnswerWithNoForCreation(): void
    {
        // Arrange
        $branchBuilderCommandMock = $this->tester->createBranchBuilderCommandMock();

        $commandTester = $this->tester->getConsoleTester($branchBuilderCommandMock);
        $commandTester->setInputs(['Branch description', 'n']);

        // Act
        $commandTester->execute(['--config' => codecept_data_dir('pattern-description.yml')]);

        // Assert
        $this->assertStringContainsString('branch-description', $commandTester->getDisplay());
        $this->assertStringContainsString('"branch-description" NOT created.', $commandTester->getDisplay());
    }
}
