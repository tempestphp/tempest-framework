<?php

declare(strict_types=1);

namespace Tempest\Support\Uri;

use Tempest\Support\Str;
use Tempest\Support\Str\ImmutableString;
use Tempest\Support\Str\MutableString;

use function parse_url;

final class Uri
{
    /**
     * The path segments as an array.
     */
    public array $segments {
        get {
            if ($this->path === null || $this->path === '' || $this->path === '/') {
                return [];
            }

            return array_values(array_filter(explode('/', $this->path), fn (string $segment) => $segment !== ''));
        }
    }

    /**
     * The parsed query parameters as an associative array.
     */
    public array $query {
        get {
            if ($this->queryString === null || $this->queryString === '') {
                return [];
            }

            parse_str($this->queryString, $query);

            return $query;
        }
    }

    /**
     * @param null|string $scheme The scheme component of the URI.
     * @param null|string $user The user component of the URI.
     * @param null|string $password The password component of the URI.
     * @param null|string $host The host component of the URI.
     * @param null|int $port The port component of the URI.
     * @param null|string $path The path component of the URI.
     * @param null|string $queryString The query string component of the URI.
     * @param null|string $fragment The fragment component of the URI.
     */
    public function __construct(
        public readonly ?string $scheme = null,
        public readonly ?string $user = null,
        public readonly ?string $password = null,
        public readonly ?string $host = null,
        public readonly ?int $port = null,
        public readonly ?string $path = null,
        public readonly ?string $queryString = null,
        public readonly ?string $fragment = null,
    ) {}

    /**
     * Creates a Uri instance from a URI string.
     */
    public static function from(string $uri): self
    {
        $parts = parse_url($uri);

        if ($parts === false) {
            return new self(path: $uri);
        }

        return new self(
            scheme: $parts['scheme'] ?? null,
            user: $parts['user'] ?? null,
            password: $parts['pass'] ?? null,
            host: $parts['host'] ?? null,
            port: $parts['port'] ?? null,
            path: $parts['path'] ?? null,
            queryString: $parts['query'] ?? null,
            fragment: $parts['fragment'] ?? null,
        );
    }

    /**
     * Returns a new Uri with the provided scheme.
     */
    public function withScheme(string $scheme): self
    {
        return $this->with(scheme: $scheme);
    }

    /**
     * Returns a new Uri with the provided user.
     */
    public function withUser(string $user): self
    {
        return $this->with(user: $user);
    }

    /**
     * Returns a new Uri with the provided password.
     */
    public function withPassword(string $password): self
    {
        return $this->with(
            user: $this->user ?? '',
            password: $password,
        );
    }

    /**
     * Returns a new Uri with the provided host.
     */
    public function withHost(string $host): self
    {
        return $this->with(host: $host);
    }

    /**
     * Returns a new Uri with the provided port.
     */
    public function withPort(int $port): self
    {
        return $this->with(port: $port);
    }

    /**
     * Returns a new Uri with the provided path.
     */
    public function withPath(string $path): self
    {
        return $this->with(path: $path);
    }

    /**
     * Returns a new Uri with the provided query parameters (replaces existing).
     */
    public function withQuery(mixed ...$query): self
    {
        return $this->with(queryString: $this->buildQueryString($query));
    }

    /**
     * Returns a new Uri with added query parameters (merges with existing).
     */
    public function addQuery(mixed ...$query): self
    {
        return $this->with(queryString: $this->buildQueryString(
            query: array_merge($this->query, $query),
        ));
    }

    /**
     * Returns a new Uri with all query parameters removed.
     */
    public function removeQuery(): self
    {
        return new self(
            scheme: $this->scheme,
            user: $this->user,
            password: $this->password,
            host: $this->host,
            port: $this->port,
            path: $this->path,
            queryString: null,
            fragment: $this->fragment,
        );
    }

    /**
     * Returns a new Uri with specific query parameters removed.
     */
    public function withoutQuery(mixed ...$query): self
    {
        $currentQuery = $this->query;

        foreach ($query as $key => $value) {
            if (is_int($key)) {
                unset($currentQuery[$value]);
            } else {
                if (isset($currentQuery[$key]) && $currentQuery[$key] === $value) {
                    unset($currentQuery[$key]);
                }
            }
        }

        $newQueryString = $this->buildQueryString($currentQuery);

        return new self(
            scheme: $this->scheme,
            user: $this->user,
            password: $this->password,
            host: $this->host,
            port: $this->port,
            path: $this->path,
            queryString: $newQueryString,
            fragment: $this->fragment,
        );
    }

    /**
     * Returns a new Uri with the provided fragment.
     */
    public function withFragment(string $fragment): self
    {
        return $this->with(fragment: $fragment);
    }

    /**
     * Returns a new Uri with the specified components changed.
     */
    private function with(
        ?string $scheme = null,
        ?string $user = null,
        ?string $password = null,
        ?string $host = null,
        ?int $port = null,
        ?string $path = null,
        ?string $queryString = null,
        array $query = [],
        ?string $fragment = null,
    ): self {
        return new self(
            scheme: $scheme ?? $this->scheme,
            user: $user ?? $this->user,
            password: $password ?? $this->password,
            host: $host ?? $this->host,
            port: $port ?? $this->port,
            path: $path ?? $this->path,
            queryString: match (true) {
                $queryString !== null => $queryString,
                $query !== [] => $this->buildQueryString($query),
                default => $this->queryString,
            },
            fragment: $fragment ?? $this->fragment,
        );
    }

    /**
     * Builds a query string from an array of query parameters.
     */
    private function buildQueryString(array $query): ?string
    {
        if ($query === []) {
            return null;
        }

        $processedQuery = [];

        foreach ($query as $key => $value) {
            if (is_int($key) && is_string($value)) {
                $processedQuery[$value] = '';
            } else {
                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                }

                $processedQuery[$key] = $value;
            }
        }

        $queryString = http_build_query($processedQuery, arg_separator: '&', encoding_type: PHP_QUERY_RFC3986);
        $queryString = preg_replace('/([^=&]+)=(?=&|$)/', replacement: '$1', subject: $queryString);

        return $queryString;
    }

    /**
     * Builds the URI string from its components.
     */
    public function toString(): string
    {
        $uri = '';

        if ($this->scheme !== null) {
            $uri .= $this->scheme . ':';
        }

        if ($this->user !== null || $this->host !== null) {
            $uri .= '//';

            if ($this->user !== null) {
                $uri .= $this->user;

                if ($this->password !== null) {
                    $uri .= ':' . $this->password;
                }

                $uri .= '@';
            }

            if ($this->host !== null) {
                $uri .= $this->host;
            }

            if ($this->port !== null) {
                $uri .= ':' . $this->port;
            }
        }

        if ($this->path !== null) {
            $path = $this->path;

            if ($this->host !== null) {
                $path = Str\ensure_starts_with($path, '/');
            }

            $uri .= $path;
        }

        if ($this->queryString !== null && $this->queryString !== '') {
            $uri .= '?' . $this->queryString;
        }

        if ($this->fragment !== null) {
            $uri .= '#' . $this->fragment;
        }

        return $uri;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toImmutableString(): ImmutableString
    {
        return new ImmutableString($this->toString());
    }

    public function toMutableString(): MutableString
    {
        return new MutableString($this->toString());
    }
}
