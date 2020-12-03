<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Brancho\Resolver;

interface ResolverDecoratorInterface extends ResolverInterface
{
    /**
     * @param \Brancho\Resolver\ConfigurableResolverInterface $resolver
     *
     * @return void
     */
    public function setResolver(ConfigurableResolverInterface $resolver): void;
}
