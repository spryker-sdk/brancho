# Brancho

Brancho is a tool which helps to create branches with a defined naming convention.

## Installation

`composer require --dev spryker-sdk/brancho`


## Configuration

After the installation you will need to configure brancho. The default configuration file is named `.brancho`.

`vendor/bin/brancho init` will create a .brancho.local file where you can configure e.g. credentials

### Pattern

Here you describe how your branch name should look like. In the pattern you can use placeholders e.g. `{resolverName}` which are filled with the result of the configured resolvers.

### Resolvers

Resolvers are used to resolve values for your placeholders. The result of the resolver is than mapped into the configured pattern. A resolver can receive information from anywhere.
To build your own resolver you need to implement the `\Brancho\Resolver\ResolverInterface`. You will then have access to :

- `\Symfony\Component\Console\Input\InputInterface`
- `\Symfony\Component\Console\Output\OutputInterface`
- `\Brancho\Context\ContextInterface`

Symfony's interfaces can be used to retrieve input data or to ask for any user input. Read more about this in [Symfony's documentation](https://symfony.com/doc/current/components/console/helpers/questionhelper.html).

Through the `\Brancho\Context\ContextInterface` you get access to the configuration and to the configured filters.

### Filters

Filters are used to filter user input into a normalized format. Think of a user input which is copied from somewhere e.g. a Ticket name or a short description. Usually these contain whitespaces and capital letters which are not allowed in git branch names.




