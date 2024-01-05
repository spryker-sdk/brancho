# Brancho

[![Build Status](https://github.com/spryker-sdk/brancho/workflows/CI/badge.svg?branch=master)](https://github.com/spryker-sdk/brancho/actions?query=workflow%3ACI+branch%3Amaster)
[![codecov](https://codecov.io/gh/spryker-sdk/brancho/branch/master/graph/badge.svg?token=L1thFB9nOG)](https://codecov.io/gh/spryker-sdk/brancho)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.3-8892BF.svg)](https://php.net/)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg?style=flat)](https://phpstan.org/)

Brancho is a tool that helps to create branches with a defined naming convention. It is also capable of committing your changes.

## Installation

It is recommended that you install Brancho globally:

`composer global require spryker-sdk/brancho`

If you want it in your project go into your project folder and run:

`composer require spryker-sdk/brancho --dev`

### Add brancho to executable path

`export PATH=/path/to/vendor/bin`

On Ubuntu 20.04 the path should be `~/.config/composer/vendor/bin`

## Configuration

After the installation you will need to configure brancho. The default configuration file is named `.brancho`. You basically only need to define the resolver that should be used.

`resolver: \Full\Qualified\ClassName`

You can also configure filters here, or you add the required filters to your resolver.

Additionally you can have a `.brancho.local` that holds configurations only needed by you e.g. credentials.


## Commands

Brancho offers the following commands:

- `brancho branch` - to create a branch.
- `brancho commit` - to commit changes.

You can use `-h` for both commands to get more information about their usage.


### Command alias

You should create aliases for both commands to make them even more easy to use.

Examples:

- `alias bb=brancho branch`
- `alias bc=brancho commit`
- `alias bca=brancho commit -a`


### Resolver

Resolvers are used to resolve branch names. A resolver can receive information from anywhere.
To build your own resolver you need to implement the `\Brancho\Resolver\ResolverInterface`. You will then have access to :

- `\Symfony\Component\Console\Input\InputInterface`
- `\Symfony\Component\Console\Output\OutputInterface`
- `\Brancho\Context\ContextInterface`

Symfony's interfaces can be used to retrieve input data or to ask for any user input. Read more about this in [Symfony's documentation](https://symfony.com/doc/current/components/console/helpers/questionhelper.html).

Through the `\Brancho\Context\ContextInterface` you get access to the configuration and to the configured filters.


### Filters

Filters are used to filter user input into a normalized format. Think of a user input which is copied from somewhere e.g. an issue name or a short description. Usually these contain whitespaces and capital letters which are not allowed in git branch names.

## Get Jira API token

Go to https://id.atlassian.com/manage-profile/security/api-tokens and create a new API token.

## Example configuration

```
resolver: \Brancho\Resolver\JiraResolver
filters:
    - \Brancho\Filter\Slugify
jira:
    host: https://spryker.atlassian.net/
    username: foo.bar@spryker.com
    password: {YOUR-API-TOKEN}
```
