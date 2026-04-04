<?php

declare(strict_types=1);

namespace Tempest\ClassVariance;

/**
 * Merges a flat list of CSS class strings, resolving conflicts
 * so that later classes override earlier ones within the same group.
 */
interface ClassMerger
{
    /**
     * Merge the given class strings, returning a single deduplicated string.
     * Conflict resolution strategy is determined by the implementation.
     */
    public function merge(string ...$classes): string;
}
