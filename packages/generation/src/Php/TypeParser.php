<?php

declare(strict_types=1);

namespace Tempest\Generation\Php;

use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType;

/**
 * Parses type declarations such as `?App\Models\User` or `(A&B)|null`, treating class names as fully qualified.
 *
 * @internal
 */
final class TypeParser
{
    private const array BUILTIN_TYPES = [
        'array',
        'bool',
        'callable',
        'false',
        'float',
        'int',
        'iterable',
        'mixed',
        'never',
        'null',
        'object',
        'static',
        'string',
        'true',
        'void',
    ];

    public static function parse(string $type): Identifier|Name|ComplexType
    {
        $type = trim($type);

        if (str_starts_with($type, '?')) {
            return new NullableType(self::parse(substr($type, 1)));
        }

        if (count($parts = self::split($type, '|')) > 1) {
            return new UnionType(array_map(self::parse(...), $parts));
        }

        if (count($parts = self::split($type, '&')) > 1) {
            return new IntersectionType(array_map(self::parse(...), $parts));
        }

        if (str_starts_with($type, '(') && str_ends_with($type, ')')) {
            return self::parse(substr($type, 1, -1));
        }

        $lowercased = strtolower($type);

        if (in_array($lowercased, self::BUILTIN_TYPES, strict: true)) {
            return new Identifier($lowercased);
        }

        if ($lowercased === 'self' || $lowercased === 'parent') {
            return new Name($lowercased);
        }

        return new Name\FullyQualified(ltrim($type, '\\'));
    }

    /**
     * @return string[]
     */
    private static function split(string $type, string $separator): array
    {
        $parts = [];
        $depth = 0;
        $current = '';

        foreach (str_split($type) as $character) {
            $depth += match ($character) {
                '(' => 1,
                ')' => -1,
                default => 0,
            };

            if ($character === $separator && $depth === 0) {
                $parts[] = $current;
                $current = '';

                continue;
            }

            $current .= $character;
        }

        $parts[] = $current;

        return $parts;
    }
}
