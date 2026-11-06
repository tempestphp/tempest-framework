<?php

declare(strict_types=1);

namespace Tempest\Database\Config;

use SensitiveParameter;
use Tempest\Database\Exceptions\DatabaseConnectionStringWasInvalid;
use Uri\Rfc3986\Uri;

final class DatabaseConnectionString
{
    /**
     * The path without its leading slash.
     */
    public ?string $database {
        get => ltrim($this->path ?? '', '/') ?: null;
    }

    /**
     * @param string $scheme The scheme, such as `mysql` or `postgres`.
     * @param null|string $host The host, or the path to a Unix socket.
     * @param null|int $port The port.
     * @param null|string $username The username.
     * @param null|string $password The password.
     * @param null|string $path The path following the authority, such as `/database` or `/path/to/database.sqlite`.
     * @param array<string,string> $parameters The query parameters, such as `sslmode` or `connect_timeout`.
     */
    public function __construct(
        public readonly string $scheme,
        public readonly ?string $host = null,
        public readonly ?int $port = null,
        public readonly ?string $username = null,
        #[SensitiveParameter]
        public readonly ?string $password = null,
        public readonly ?string $path = null,
        public readonly array $parameters = [],
    ) {}

    /**
     * Creates a DatabaseConnectionString instance from a connection string, such as `postgres://user:password@host:5432/database?sslmode=require`. Components are percent-decoded, so a Unix socket may be given as a percent-encoded host.
     */
    public static function from(#[SensitiveParameter] string $connectionString): self
    {
        $uri = Uri::parse($connectionString);

        if ($uri?->getScheme() === null) {
            throw DatabaseConnectionStringWasInvalid::couldNotBeParsed();
        }

        parse_str($uri->getQuery() ?? '', $parameters);

        return new self(
            scheme: $uri->getScheme(),
            host: self::decode($uri->getHost()),
            port: $uri->getPort(),
            username: self::decode($uri->getUsername()),
            password: self::decode($uri->getPassword()),
            path: self::decode($uri->getPath()),
            parameters: array_filter($parameters, is_string(...)),
        );
    }

    private static function decode(?string $component): ?string
    {
        if ($component === null || $component === '') {
            return null;
        }

        return rawurldecode($component);
    }
}
