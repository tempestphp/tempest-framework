<?php

declare(strict_types=1);

namespace Tempest\ClassVariance;

use Tempest\ClassVariance\Classmaps\Classmap;

/**
 * Merges CSS class strings using a ClassGroupMap for conflict resolution.
 *
 * Algorithm (mirrors tailwind-merge):
 *   1. Strip any Tailwind prefix (e.g. 'tw-') from the base class.
 *   2. Extract variant modifiers (e.g. 'hover:', 'dark:', 'lg:') from the class.
 *      Modifiers are separated by $separator (default ':') outside of [] brackets.
 *   3. Look up the base class in the ClassGroupMap to find its group ID.
 *   4. The deduplication key is modifier_string + group_id.
 *   5. When a class is stored, all previously stored classes whose key corresponds
 *      to a group listed in conflictingClassGroups[group_id] are removed.
 *   6. Classes whose group is unknown are kept as-is (they cannot conflict).
 *   7. Last class for each key wins.
 *
 * Use this merger via tv() for Tailwind CSS class strings.
 * For non-Tailwind class strings use SeparatorClassMerger via cv() instead.
 */
final readonly class GroupClassMerger implements ClassMerger
{
    public function __construct(
        private Classmap $classGroups,
        private string $prefix = '',
        private string $separator = ':',
    ) {}

    public function merge(string ...$classes): string
    {
        /** @var array<string, string> $resolved  key => original class string */
        $resolved = [];

        foreach ($classes as $class) {
            if ($class === '') {
                continue;
            }

            [$modifiers, $baseClass] = $this->extractModifiers($class);

            $withoutPrefix = $this->stripPrefix($baseClass);

            $groupId = $this->classGroups->findGroup($withoutPrefix);

            if ($groupId === null) {
                // Unknown class — keep it, keyed by the full string so it is never
                // displaced by another unknown class that happens to share a name.
                $resolved['__unknown__' . $class] = $class;
                continue;
            }

            $key = $modifiers . ':' . $groupId;

            // Remove classes from groups that conflict with the incoming group.
            foreach ($this->classGroups->conflictingClassGroups[$groupId] ?? [] as $conflictId) {
                $conflictKey = $modifiers . ':' . $conflictId;
                unset($resolved[$conflictKey]);
            }

            $resolved[$key] = $class;
        }

        return implode(' ', array_values($resolved));
    }

    /**
     * Split a class string into [modifiers, baseClass].
     * Separators inside [] brackets (arbitrary values) are ignored.
     *
     * Example: 'dark:hover:bg-red-500' → ['dark:hover', 'bg-red-500']
     * Example: 'p-[color:red]'         → ['', 'p-[color:red]']
     *
     * @return array{0: string, 1: string}
     */
    private function extractModifiers(string $class): array
    {
        if ($this->separator === '' || ! str_contains($class, $this->separator)) {
            return ['', $class];
        }

        $sepLen = strlen($this->separator);
        $depth = 0;
        $lastPos = -1;
        $len = strlen($class);

        for ($i = 0; $i < $len; $i++) {
            if ($class[$i] === '[') {
                $depth++;
            } elseif ($class[$i] === ']') {
                $depth--;
            } elseif ($depth === 0 && substr($class, $i, $sepLen) === $this->separator) {
                $lastPos = $i;
            }
        }

        if ($lastPos === -1) {
            return ['', $class];
        }

        return [
            substr($class, 0, $lastPos),
            substr($class, $lastPos + $sepLen),
        ];
    }

    private function stripPrefix(string $class): string
    {
        if ($this->prefix === '' || ! str_starts_with($class, $this->prefix)) {
            return $class;
        }

        return substr($class, strlen($this->prefix));
    }
}
