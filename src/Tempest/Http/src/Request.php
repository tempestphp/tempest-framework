<?php

declare(strict_types=1);

namespace Tempest\Http;

use function Tempest\get;
use Tempest\Http\Cookie\Cookie;
use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\Session\Session;

// TODO: Finalize!
class Request
{
    private(set) public string $path;
    private(set) public array $query;

    /** @var \Tempest\Http\Upload[] */
    private(set) public array $files;

    public function __construct(
        private(set) Method $method,
        private(set) string $uri,
        private(set) array $body = [],
        private(set) array $headers = [],
    ) {
        $this->path ??= $this->resolvePath();
        $this->query ??= $this->resolveQuery();
        $this->files ??= [];
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

        return $this->hasQuery($key);
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
