# Brancho

[![Build Status](https://github.com/spryker-sdk/brancho/workflows/CI/badge.svg?branch=master)](https://github.com/spryker-sdk/brancho/actions?query=workflow%3ACI+branch%3Amaster)
[![codecov](https://codecov.io/gh/spryker-sdk/brancho/branch/master/graph/badge.svg?token=L1thFB9nOG)](https://codecov.io/gh/spryker-sdk/brancho)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.3-8892BF.svg)](https://php.net/)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg?style=flat)](https://phpstan.org/)

Brancho is a tool which helps to create branches with a defined naming convention.

## Installation

`composer require --dev spryker-sdk/brancho`


## Configuration

After the installation you will need to configure brancho. The default configuration file is named `.brancho`.


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
