<?php

declare(strict_types=1);

namespace Tempest\ClassVariance\Classmaps;

/**
 * Immutable map of CSS class group definitions and their conflict rules.
 *
 * Each entry in $classGroups maps a group ID to a list of matchers.
 * Three matcher shapes are supported:
 *
 *   string                         → exact class name match
 *                                    e.g. 'block' matches only 'block'
 *
 *   ['prefix']                     → wildcard prefix match
 *                                    e.g. ['p'] matches 'p', 'p-4', 'p-[2rem]'
 *
 *   ['prefix' => ['v1', 'v2']]    → constrained prefix match
 *                                    matches 'prefix-v1', 'prefix-v2', etc.
 *                                    Use '' as a suffix to match the bare prefix
 *                                    e.g. ['border' => ['', '2', '4']] matches
 *                                    'border', 'border-2', 'border-4'
 *
 * Iteration order determines priority: the first group whose matcher fires wins.
 * Constrained prefix groups should come before wildcard prefix groups when both
 * share the same prefix (e.g. font-size before text-color for the 'text' prefix).
 *
 * $conflictingClassGroups maps a group ID to the list of group IDs it supersedes.
 * When a class from group A is encountered during merge, all previously accumulated
 * classes from groups listed under A are removed.
 *
 * Use extend() to merge additional definitions on top of an existing map (additive),
 * or override() to replace specific group definitions entirely.
 */
final readonly class Classmap
{
    /**
     * @param array<string, list<string|array<string, list<string>>|array{0: string}>> $classGroups
     * @param array<string, list<string>> $conflictingClassGroups
     */
    public function __construct(
        public array $classGroups = [],
        public array $conflictingClassGroups = [],
    ) {}

    /**
     * Find the group ID for the given class, or null if unknown.
     */
    public function findGroup(string $class): ?string
    {
        foreach ($this->classGroups as $groupId => $matchers) {
            foreach ($matchers as $matcher) {
                if (is_string($matcher)) {
                    // Exact match
                    if ($matcher === $class) {
                        return $groupId;
                    }

                    continue;
                }

                if (! is_array($matcher)) {
                    continue;
                }

                if (array_is_list($matcher)) {
                    // ['prefix'] — wildcard: matches prefix itself or prefix-{anything}
                    $prefix = $matcher[0];

                    if ($class === $prefix || str_starts_with($class, $prefix . '-')) {
                        return $groupId;
                    }
                } else {
                    // ['prefix' => ['suffix1', 'suffix2']] — constrained suffix list
                    $prefix = array_key_first($matcher);
                    $suffixes = $matcher[$prefix];

                    foreach ($suffixes as $suffix) {
                        if ($suffix === '' && $class === $prefix) {
                            return $groupId;
                        }

                        if ($suffix !== '' && $class === $prefix . '-' . $suffix) {
                            return $groupId;
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * Return a new map with $additions merged in.
     * For groups that exist in both maps, matchers are concatenated (additive).
     * Conflicting group lists are merged by union.
     */
    public function extend(self $additions): self
    {
        $groups = $this->classGroups;

        foreach ($additions->classGroups as $groupId => $matchers) {
            $groups[$groupId] = array_merge($groups[$groupId] ?? [], $matchers);
        }

        $conflicts = $this->conflictingClassGroups;

        foreach ($additions->conflictingClassGroups as $groupId => $conflicting) {
            $conflicts[$groupId] = array_values(array_unique([
                ...($conflicts[$groupId] ?? []),
                ...$conflicting,
            ]));
        }

        return new self($groups, $conflicts);
    }

    /**
     * Return a new map with specific groups replaced entirely by $replacements.
     * Groups not present in $replacements are kept as-is.
     */
    public function override(self $replacements): self
    {
        return new self(
            array_replace($this->classGroups, $replacements->classGroups),
            array_replace($this->conflictingClassGroups, $replacements->conflictingClassGroups),
        );
    }
}
