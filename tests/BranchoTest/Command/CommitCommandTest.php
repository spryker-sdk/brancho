<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace BranchoTest\Command;

use Brancho\BranchoFactory;
use Brancho\Command\CommitCommand;
use Brancho\Commit\CommitMessageResolver;
use Codeception\Stub;
use Codeception\Test\Unit;

/**
 * @group Brancho
 * @group Command
 * @group CommitCommandTest
 */
class CommitCommandTest extends Unit
{
    /**
     * @var \BranchoTest\CommandTester
     */
    protected $tester;

    /**
     * @return array
     */
    public function branchNameProvider(): array
    {
        return [
            'bug branch' => ['bugfix/rk-123-bug-summary', 'RK-123 Commit message.'],
            'epic branch' => ['feature/rk-123/epic-summary', 'RK-123 Commit message.'],
            'story branch' => ['feature/rk-123/rk-234/story-summary', 'RK-234 Commit message.'],
            'task branch' => ['feature/rk-123/rk-234/rk-345/task-summary', 'RK-345 Commit message.'],
            'sub-task branch' => ['feature/rk-123/rk-234/rk-456/task-summary', 'RK-456 Commit message.'],
        ];
    }

    /**
     * @dataProvider branchNameProvider
     *
     * @param string $branchName
     * @param string $expectedCommitMessage
     *
     * @return void
     */
    public function testCommitMessageContainsPassedMessageAndIsPrefixedWithJiraIssueFrom(string $branchName, string $expectedCommitMessage): void
    {
        // Arrange
        $commitCommandMock = $this->createCommitCommandMock($branchName);
        $commandTester = $this->tester->getConsoleTester($commitCommandMock);

        // Act
        $commandTester->execute([
            '--config' => codecept_data_dir('commit-configuration.yml'),
            '--message' => 'Commit message',
        ]);

        // Assert
        $this->assertStringContainsString($expectedCommitMessage, $commandTester->getDisplay());
    }

    /**
     * @return void
     */
    public function testCommitMessageContainsSelectedMessageAndIsPrefixedWithJiraIssue(): void
    {
        // Arrange
        $commitCommandMock = $this->createCommitCommandMock('feature/rk-123/epic-summary');
        $commandTester = $this->tester->getConsoleTester($commitCommandMock);

        // Act
        $commandTester->setInputs([0]);
        $commandTester->execute([
            '--config' => codecept_data_dir('commit-configuration.yml'),
        ]);

        // Assert
        $this->assertStringContainsString('Please select from default messages', $commandTester->getDisplay());
        $this->assertStringContainsString('RK-123 Selected message.', $commandTester->getDisplay());
    }

    /**
     * @return void
     */
    public function testCommitMessageContainsOwnMessageWhenSelectedToWriteOwnAndIsPrefixedWithJiraIssue(): void
    {
        // Arrange
        $commitCommandMock = $this->createCommitCommandMock('feature/rk-123/epic-summary');
        $commandTester = $this->tester->getConsoleTester($commitCommandMock);

        // Act
        $commandTester->setInputs([1, 'Own message']);
        $commandTester->execute([
            '--config' => codecept_data_dir('commit-configuration.yml'),
        ]);

        // Assert
        $this->assertStringContainsString('Please select from default messages', $commandTester->getDisplay());
        $this->assertStringContainsString('RK-123 Own message.', $commandTester->getDisplay());
    }

    /**
     * @return void
     */
    public function testErrorMessageIsShownWhenJiraIssueCanNotBeExtractedFromCurrentBranchName(): void
    {
        // Arrange
        $commitCommandMock = $this->createCommitCommandMock('branch-name-without-jira-issue');
        $commandTester = $this->tester->getConsoleTester($commitCommandMock);

        // Act
        $commandTester->execute([
            '--config' => codecept_data_dir('commit-configuration.yml'),
        ]);

        // Assert
        $this->assertStringContainsString('The resolved commit message is empty, something went wrong.', $commandTester->getDisplay());
    }

    /**
     * @group single
     *
     * @return void
     */
    public function testCommitCanBeAborted(): void
    {
        // Arrange
        $commitCommandMock = $this->createCommitCommandMock('feature/rk-123/epic-summary');
        $commandTester = $this->tester->getConsoleTester($commitCommandMock);

        // Act
        $commandTester->setInputs(['no']);
        $commandTester->execute([
            '--config' => codecept_data_dir('commit-configuration.yml'),
            '--message' => 'Commit message',
        ]);

        // Assert
        $this->assertStringContainsString('Changes "RK-123 Commit message." NOT committed.', $commandTester->getDisplay());
    }

    /**
     * @param string $branchName
     *
     * @return \Brancho\Command\CommitCommand
     */
    protected function createCommitCommandMock(string $branchName): CommitCommand
    {
        /** @var \Brancho\Command\CommitCommand $commitCommandMock */
        $commitCommandMock = Stub::construct(CommitCommand::class, [], [
            'commitChanges' => function () {
            },
        ]);

        $commitMessageResolverMock = Stub::make(CommitMessageResolver::class, [
            'getBranchName' => function () use ($branchName) {
                return $branchName;
            },
        ]);

        /** @var \Brancho\BranchoFactory $factoryMock */
        $factoryMock = Stub::make(BranchoFactory::class, [
            'createCommitMessageResolver' => function () use ($commitMessageResolverMock) {
                return $commitMessageResolverMock;
            },
        ]);

        $commitCommandMock->setFactory($factoryMock);

        return $commitCommandMock;
    }
}
