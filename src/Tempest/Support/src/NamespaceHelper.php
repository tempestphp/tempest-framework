<?php

declare(strict_types=1);

namespace Tempest\Support;

final readonly class NamespaceHelper
{
    protected const NAMESPACE_SEPARATOR = '\\';
    
    /**
     * This will build a namespace from the given path or namespace.
     * It's compatible with PSR-4.
     *
     * @param string ...$namespaces The unformatted namespaces or paths.
     *
     * @return string A compatible PSR-4 namespace.
     */
    public static function make(string ...$namespaces): string
    {
        // Split namespaces items on forward and backward slashes
        $parts = array_reduce($namespaces, fn (array $carry, string $part) => [...$carry, ...explode('/', $part)], []);
        $parts = array_reduce($parts, fn (array $carry, string $part) => [...$carry, ...explode(self::NAMESPACE_SEPARATOR, $part)], []);

        // Trim forward and backward slashes
        $parts = array_map(fn (string $part) => trim($part, DIRECTORY_SEPARATOR . self::NAMESPACE_SEPARATOR), $parts);
        $parts = array_filter($parts);
        
        // pascal case each part to validate PSR-4
        $parts = array_map(fn (string $part) => str($part)->pascal()->toString(), $parts);

        // Glue parts together
        $namespace = implode(self::NAMESPACE_SEPARATOR, $parts);

        return $namespace;
    }
}
