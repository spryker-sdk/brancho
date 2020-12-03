<?php

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
