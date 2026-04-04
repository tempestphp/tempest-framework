<?php

declare(strict_types=1);

namespace Tempest\ClassVariance\Config;

use Tempest\ClassVariance\ClassMerger;

/**
 * Configuration object that supplies a ClassMerger to cv() and tv().
 *
 * Implement this interface to provide a completely custom merge strategy.
 * For the built-in strategies use GenericClassVarianceConfig (cv)
 * or TailwindClassVarianceConfig (tv).
 */
interface ClassVarianceConfig
{
    public ClassMerger $merger { get; }
}
