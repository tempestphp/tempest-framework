<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Http\Cookie\Cookie;
use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\Session\Session;
use Tempest\Validation\SkipValidation;

use function Tempest\get;
use function Tempest\Support\Arr\get_by_key;
use function Tempest\Support\Arr\has_key;

/** @phpstan-require-implements \Tempest\Http\Request */
trait IsRequest
{
    #[SkipValidation]
    private(set) Method $method;

    #[SkipValidation]
    private(set) string $uri;

    #[SkipValidation]
    private(set) ?string $raw = null;

    #[SkipValidation]
    private(set) array $body = [];

    #[SkipValidation]
    private(set) RequestHeaders $headers;

    #[SkipValidation]
    private(set) string $path;

    #[SkipValidation]
    private(set) array $query;

    /** @var \Tempest\Http\Upload[] */
    #[SkipValidation]
    private(set) array $files;

    #[SkipValidation]
    public Session $session {
        get => get(Session::class);
    }

    #[SkipValidation]
    public array $cookies = [];

    public function __construct(
        Method $method,
        string $uri,
        array $body = [],
        array $headers = [],
        array $files = [],
        ?string $raw = null,
    ) {
        $this->method = $method;
        $this->uri = $uri;
        $this->body = $body;
        $this->headers = RequestHeaders::normalizeFromArray($headers);
        $this->files = $files;
        $this->raw = $raw;

        $this->path ??= $this->resolvePath();
        $this->query ??= $this->resolveQuery();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->body)) {
            return get_by_key($this->body, $key);
        }

        if (array_key_exists($key, $this->query)) {
            return get_by_key($this->query, $key);
        }

        return $default;
    }

    public function getSessionValue(string $name): mixed
    {
        /** @var Session $session */
        $session = get(Session::class);

        return $session->get($name);
    }

    public function getCookie(string $name): ?Cookie
    {
        return $this->cookies[$name] ?? null;
    }

    private function resolvePath(): string
    {
        $decodedUri = rawurldecode($this->uri);
        $parsedUrl = parse_url($decodedUri);

        return $parsedUrl['path'] ?? '';
    }

    private function resolveQuery(): array
    {
        $decodedUri = rawurldecode($this->uri);
        $parsedUrl = parse_url($decodedUri);
        $queryString = $parsedUrl['query'] ?? '';

        parse_str($queryString, $query);

        return $query;
    }

    public function has(string $key): bool
    {
        if ($this->hasBody($key)) {
            return true;
        }

        return $this->hasQuery($key);
    }

    public function hasBody(?string $key = null): bool
    {
        if ($key) {
            return has_key($this->body, $key);
        }

        return count($this->body) || ((bool) $this->raw);
    }

    public function hasQuery(string $key): bool
    {
        return has_key($this->query, $key);
    }

    public function withMethod(Method $method): self
    {
        $clone = clone $this;

        $clone->method = $method;

        return $clone;
    }
}
