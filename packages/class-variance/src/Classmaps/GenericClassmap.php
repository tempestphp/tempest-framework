<?php

declare(strict_types=1);

namespace Tempest\ClassVariance\Classmaps;

/**
 * An empty Classmap — the default starting point for cv().
 *
 * No groups are declared, so the SeparatorClassMerger falls back purely to
 * its separator heuristic (everything before the first '-' is the group key).
 *
 * Pass a Classmap with explicit groups to GenericClassVarianceConfig when you
 * need conflict resolution for classes that share no common prefix.
 */
final class GenericClassmap
{
    public static function default(): Classmap
    {
        return new Classmap();
    }
}
