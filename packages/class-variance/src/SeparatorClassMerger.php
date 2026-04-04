<?php

declare(strict_types=1);

namespace Tempest\ClassVariance;

use Tempest\ClassVariance\Classmaps\Classmap;

/**
 * Merges CSS class strings using a configurable separator to determine groups.
 *
 * The group key for a class is everything before its first occurrence of
 * $separator (default '-'). Classes with no separator are treated as singleton
 * groups and do not conflict with anything unless explicitly declared in
 * $classGroups.
 *
 * An optional ClassGroupMap can declare explicit groups (e.g. display utilities
 * like 'block' / 'flex' / 'hidden' that conflict without sharing a prefix).
 *
 * Within each group, last-in wins.
 *
 * This merger makes no assumption about Tailwind CSS. Use GroupClassMerger
 * (via tv()) for full Tailwind-aware conflict resolution.
 */
final readonly class SeparatorClassMerger implements ClassMerger
{
    public function __construct(
        private string $separator = '-',
        private ?Classmap $classGroups = null,
    ) {}

    public function merge(string ...$classes): string
    {
        /** @var array<string, string> $resolved group-key => class */
        $resolved = [];

        foreach ($classes as $class) {
            if ($class === '') {
                continue;
            }

            $groupKey = $this->resolveGroup($class);
            $resolved[$groupKey] = $class;
        }

        return implode(' ', array_values($resolved));
    }

    private function resolveGroup(string $class): string
    {
        // Explicit group map takes priority over separator heuristic.
        if ($this->classGroups !== null) {
            $group = $this->classGroups->findGroup($class);

            if ($group !== null) {
                return $group;
            }
        }

        $pos = strpos($class, $this->separator);

        if ($pos === false) {
            // No separator — class is its own group (won't conflict with others).
            return $class;
        }

        return substr($class, 0, $pos);
    }
}
