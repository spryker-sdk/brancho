<?php

namespace BranchoTest\BranchoTest\Command;

use Brancho\BranchoFactory;
use Brancho\Command\BranchBuilderCommand;
use Brancho\Jira\Jira;
use Codeception\Stub;
use Codeception\Test\Unit;

class BranchBuilderCommandTest extends Unit
{
    /**
     * @var CommandTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function testSimplePattern(): void
    {
        $branchBuilderCommand = new BranchBuilderCommand();

        $commandTester = $this->tester->getConsoleTester($branchBuilderCommand);
        $commandTester->setInputs(['Branch description']);
        $commandTester->execute(['--config' => codecept_data_dir('pattern-description.yml')]);

        $this->assertStringContainsString('branch-description', $commandTester->getDisplay());
        $this->assertStringContainsString('"branch-description" created.', $commandTester->getDisplay());
    }

    /**
     * @return void
     */
    public function testCommandTypePattern(): void
    {
        $branchBuilderCommand = new BranchBuilderCommand();

        $commandTester = $this->tester->getConsoleTester($branchBuilderCommand);
        $commandTester->setInputs(['feature', 'Branch description']);
        $commandTester->execute(['--config' => codecept_data_dir('pattern-type-and-description.yml')]);

        $this->assertStringContainsString('feature/branch-description', $commandTester->getDisplay());
        $this->assertStringContainsString('"feature/branch-description" created.', $commandTester->getDisplay());
    }

    /**
     * @return void
     */
    public function testCommandJiraPattern(): void
    {
        $jiraMock = Stub::make(Jira::class, [
            'getJiraIssue' => function () {
                return include codecept_data_dir('jira-bug-response.php');
            },
        ]);

        /** @var $factoryMock BranchoFactory */
        $factoryMock = Stub::make(BranchoFactory::class, [
            'createJira' => function () use ($jiraMock) {
                return $jiraMock;
            },
        ]);

        $branchBuilderCommand = new BranchBuilderCommand();
        $branchBuilderCommand->setFactory($factoryMock);

        $commandTester = $this->tester->getConsoleTester($branchBuilderCommand);
        $commandTester->setInputs(['rk-123']);
        $commandTester->execute(['--config' => codecept_data_dir('pattern-jira.yml')]);

        $this->assertStringContainsString('bugfix/rk-123/master-ticket-summary', $commandTester->getDisplay());
        $this->assertStringContainsString('"bugfix/rk-123/master-ticket-summary" created.', $commandTester->getDisplay());
    }

    /**
     * @return void
     */
    public function testCommandJiraPatternWithErrorResponse(): void
    {
        $jiraMock = Stub::make(Jira::class, [
            'getJiraIssue' => function () {
                return include codecept_data_dir('jira-error-response.php');
            },
        ]);

        /** @var $factoryMock BranchoFactory */
        $factoryMock = Stub::make(BranchoFactory::class, [
            'createJira' => function () use ($jiraMock) {
                return $jiraMock;
            },
        ]);

        $branchBuilderCommand = new BranchBuilderCommand();
        $branchBuilderCommand->setFactory($factoryMock);

        $commandTester = $this->tester->getConsoleTester($branchBuilderCommand);
        $commandTester->setInputs(['rk-123']);
        $commandTester->execute(['--config' => codecept_data_dir('pattern-jira.yml')]);

        $this->assertStringContainsString(
            'Issue does not exist or you do not have permission to see it.',
            $commandTester->getDisplay()
        );
    }

    /**
     * @return void
     */
    public function testExecuteOnlyShowsBranchName(): void
    {
        $branchBuilderCommand = new BranchBuilderCommand();

        $commandTester = $this->tester->getConsoleTester($branchBuilderCommand);
        $commandTester->setInputs(['feature', 'Branch description', 'n']);
        $commandTester->execute(['--config' => codecept_data_dir('pattern-type-and-description.yml')]);

        $this->assertStringContainsString('feature/branch-description', $commandTester->getDisplay());
        $this->assertStringContainsString('"feature/branch-description" NOT created.', $commandTester->getDisplay());
    }
}
