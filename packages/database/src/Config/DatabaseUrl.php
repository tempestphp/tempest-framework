<?php

declare(strict_types=1);

namespace Tempest\Database\Config;

use SensitiveParameter;
use Tempest\Database\Exceptions\DatabaseUrlWasInvalid;

/**
 * @internal
 */
final class DatabaseUrl
{
    /**
     * Parses a database URL into named constructor arguments. Components missing from the URL are omitted so the constructor defaults apply.
     *
     * @param string[] $schemes
     * @return array<string,string>
     */
    public static function parse(#[SensitiveParameter] string $url, array $schemes): array
    {
        $parts = parse_url($url);

        if ($parts === false || ! isset($parts['host'])) {
            throw DatabaseUrlWasInvalid::couldNotBeParsed();
        }

        $scheme = $parts['scheme'] ?? null;

        if (! in_array($scheme, $schemes, strict: true)) {
            throw DatabaseUrlWasInvalid::schemeWasNotSupported($scheme, $schemes);
        }

        $database = ltrim($parts['path'] ?? '', '/');

        return array_filter(
            [
                'host' => $parts['host'],
                'port' => isset($parts['port']) ? (string) $parts['port'] : null,
                'username' => isset($parts['user']) ? rawurldecode($parts['user']) : null,
                'password' => isset($parts['pass']) ? rawurldecode($parts['pass']) : null,
                'database' => $database !== '' ? rawurldecode($database) : null,
            ],
            fn (?string $value) => $value !== null,
        );
    }
}
