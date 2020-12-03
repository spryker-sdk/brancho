<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Brancho\Filter;

use Cocur\Slugify\Slugify as Slugifier;
use Laminas\Filter\FilterInterface;

class Slugify implements FilterInterface
{
    /**
     * @param mixed $value
     *
     * @return string
     */
    public function filter($value): string
    {
        $slugifier = new Slugifier();

        return $slugifier->slugify($value);
    }
}
