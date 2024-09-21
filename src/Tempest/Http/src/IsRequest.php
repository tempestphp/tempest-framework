<?php

declare(strict_types=1);

namespace Tempest\Http;

use function Tempest\get;
use Tempest\Http\Cookie\Cookie;
use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\Session\Session;

/** @phpstan-require-implements \Tempest\Http\Request */
trait IsRequest
{
    public string $path;

    public array $query;

    /** @var \Tempest\Http\Upload[] */
    public array $files;

    public function __construct(
        public Method $method,
        public string $uri,
        public array $body = [],
        public array $headers = [],
    ) {
        $this->path ??= $this->resolvePath();
        $this->query ??= $this->resolveQuery();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->body)) {
            return $this->body[$key];
        }

        if (array_key_exists($key, $this->query)) {
            return $this->query[$key];
        }

        return $default;
    }

    public function getMethod(): Method
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): array
    {
        return $this->query;
    }

    /** @return \Tempest\Http\Upload[] */
    public function getFiles(): array
    {
        return $this->files;
    }

    public function getSessionValue(string $name): mixed
    {
        /** @var Session $session */
        $session = get(Session::class);

        return $session->get($name);
    }

    public function getCookie(string $name): ?Cookie
    {
        /** @var CookieManager $cookies */
        $cookies = get(CookieManager::class);

        return $cookies->get($name);
    }

    public function getCookies(): array
    {
        /** @var CookieManager $cookies */
        $cookies = get(CookieManager::class);

        return $cookies->all();
    }

    public function validate(): void
    {
        // No additional validation done
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

        return (bool) $this->hasQuery($key);
    }

    public function hasBody(string $key): bool
    {
        return array_key_exists($key, $this->body);
    }

    public function hasQuery(string $key): bool
    {
        return array_key_exists($key, $this->query);
    }
}
