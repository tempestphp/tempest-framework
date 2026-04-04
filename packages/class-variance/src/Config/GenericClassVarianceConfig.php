<?php

declare(strict_types=1);

namespace Tempest\ClassVariance\Config;

use Tempest\ClassVariance\Classmaps\Classmap;
use Tempest\ClassVariance\ClassMerger;
use Tempest\ClassVariance\SeparatorClassMerger;

/**
 * Configuration for cv() — separator-based class merging with no Tailwind dependency.
 *
 * The group key for each class is everything before the first occurrence of
 * $separator (default '-'). Within a group, last class wins.
 *
 * An optional $classGroups map can declare explicit conflict groups for classes
 * that share no common prefix (e.g. display utilities: block / flex / hidden).
 */
final class GenericClassVarianceConfig implements ClassVarianceConfig
{
    public function __construct(
        public readonly string $separator = '-',
        public readonly ?Classmap $classGroups = null,
    ) {}

    public ClassMerger $merger {
        get => new SeparatorClassMerger($this->separator, $this->classGroups);
    }
}
