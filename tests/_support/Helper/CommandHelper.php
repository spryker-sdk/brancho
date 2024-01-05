<?php

namespace BranchoTest\Helper;

use Brancho\BranchoBootstrap;
use Brancho\BranchoFactory;
use Brancho\Command\BranchBuilderCommand;
use Brancho\Jira\Jira;
use Brancho\Resolver\ResolverDecorator;
use Codeception\Module;
use Codeception\Stub;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class CommandHelper extends Module
{
    /**
     * @return void
     */
    public function _initialize(): void
    {
        defined('ROOT_DIR') || define('ROOT_DIR', getcwd());
    }

    /**
     * @param \Symfony\Component\Console\Command\Command $command
     *
     * @return \Symfony\Component\Console\Tester\CommandTester
     */
    public function getConsoleTester(Command $command): CommandTester
    {
        $application = new BranchoBootstrap();
        $application->add($command);

        $command = $application->find($command->getName());

        return new CommandTester($command);
    }

    /**
     * @param array $jiraResponseDataFiles
     * @param $rootDirectoryMock
     * @return BranchBuilderCommand
     * @throws \Exception
     */
    public function haveBranchBuilderCommandMock(array $jiraResponseDataFiles, $rootDirectoryMock = null)
    {
        $jiraReponses = array_map(function($file){
            return include codecept_data_dir($file);
        }, $jiraResponseDataFiles);

        $jiraMock = Stub::make(Jira::class, [
            'getJiraIssue' => Stub::consecutive(...$jiraReponses),
        ]);

        $branchoFactoryMockMethods = [
            'createJira' => $jiraMock,
        ];

        if ($rootDirectoryMock) {
            $resolverDecoratorMock = Stub::make(ResolverDecorator::class, [
                'getRootDirectory' => $rootDirectoryMock->url(),
            ]);

            $branchoFactoryMockMethods['createResolverDecorator'] = $resolverDecoratorMock;
        }

        /** @var \Brancho\BranchoFactory $factoryMock */
        $factoryMock = Stub::make(BranchoFactory::class, $branchoFactoryMockMethods);

        $branchBuilderCommandMock = $this->createBranchBuilderCommandMock();
        $branchBuilderCommandMock->setFactory($factoryMock);

        return $branchBuilderCommandMock;
    }

    /**
     * @return \Brancho\Command\BranchBuilderCommand
     */
    public function createBranchBuilderCommandMock(): BranchBuilderCommand
    {
        /** @var \Brancho\Command\BranchBuilderCommand $branchBuilderCommandMock */
        $branchBuilderCommandMock = Stub::construct(BranchBuilderCommand::class, [], [
            'createBranch' => function () {
            },
        ]);

        return $branchBuilderCommandMock;
    }

    /**
     * @param $commandTester
     * @return void
     */
    public function assertCommandAskedCredentials($commandTester)
    {
        $this->assertStringContainsString(
            'Please enter the host of your Jira e.g. https://spryker.atlassian.net',
            $commandTester->getDisplay()
        );

        $this->assertStringContainsString(
            'Please enter your Jira username',
            $commandTester->getDisplay()
        );

        $this->assertStringContainsString(
            'Please enter you Jira API key, you can get one here ' .
            'https://id.atlassian.com/manage-profile/security/api-tokens',
            $commandTester->getDisplay()
        );
    }
}
