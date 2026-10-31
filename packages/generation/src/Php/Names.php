<?php

declare(strict_types=1);

namespace Tempest\Generation\Php;

use PhpParser\Node\Name;

/**
 * @internal
 */
final class Names
{
    public const string RESOLVED_NAME_ATTRIBUTE = 'resolvedName';

    /**
     * Returns the fully qualified name a name refers to, or `null` when it is relative to an unknown context.
     */
    public static function resolve(Name $name): ?string
    {
        if ($name->isFullyQualified()) {
            return $name->toString();
        }

        $resolved = $name->getAttribute(self::RESOLVED_NAME_ATTRIBUTE);

        return $resolved instanceof Name ? $resolved->toString() : null;
    }

    public static function refersTo(Name $name, string $fullyQualifiedName): bool
    {
        return strcasecmp(self::resolve($name) ?? $name->toString(), ltrim($fullyQualifiedName, '\\')) === 0;
    }

    public static function lastSegment(string $name): string
    {
        $position = strrpos($name, '\\');

        return $position === false ? $name : substr($name, $position + 1);
    }

    public static function namespaceOf(string $name): string
    {
        $position = strrpos($name, '\\');

        return $position === false ? '' : substr($name, 0, $position);
    }
}
