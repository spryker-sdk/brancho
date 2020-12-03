<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Brancho\Resolver;

use Brancho\Context\ContextInterface;

interface ConfigurableResolverInterface extends ResolverInterface
{
    /**
     * Returns if the resolver is properly configured.
     *
     * @param \Brancho\Context\ContextInterface $context
     *
     * @return bool
     */
    public function isConfigured(ContextInterface $context): bool;

    /**
     * Returns an array with a key for the resolver configuration to be used and an array with
     * configuration key as key and a question as value.
     *
     * @example:
     * ```
     * $configInformation = [
     *    'resolverConfigurationKey' => [
     *        'configurationKey' => 'question the user is asked to get the input',
     *    ]
     * ];
     * ```
     *
     * @return array
     */
    public function getConfigurationInformation(): array;
}
