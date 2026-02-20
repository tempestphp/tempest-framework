<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Container\Singleton;
use Tempest\Http\Cookie\Cookie;

#[Singleton]
final class OpaqueRequest implements Request
{
    public function __construct(
        private RequestHolder $requestHolder,
    ) {}

    public array $cookies {
        get {
            return $this->requestHolder->request->cookies;
        }
    }

    public array $files {
        get {
            return $this->requestHolder->request->files;
        }
    }

    public array $query {
        get {
            return $this->requestHolder->request->query;
        }
    }

    public string $path {
        get {
            return $this->requestHolder->request->path;
        }
    }
    public RequestHeaders $headers {
        get {
            return $this->requestHolder->request->headers;
        }
    }

    public array $body {
        get {
            return $this->requestHolder->request->body;
        }
    }

    public ?string $raw {
        get {
            return $this->requestHolder->request->raw;
        }
    }

    public string $uri {
        get {
            return $this->requestHolder->request->uri;
        }
    }

    public Method $method {
        get {
            return $this->requestHolder->request->method;
        }
    }

    public function has(string $key): bool
    {
        return $this->requestHolder->request->has($key);
    }

    public function hasBody(?string $key = null): bool
    {
        return $this->requestHolder->request->hasBody($key);
    }

    public function hasQuery(string $key): bool
    {
        return $this->requestHolder->request->hasQuery($key);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->requestHolder->request->get($key, $default);
    }

    public function getSessionValue(string $name): mixed
    {
        return $this->requestHolder->request->getSessionValue($name);
    }

    public function getCookie(string $name): ?Cookie
    {
        return $this->requestHolder->request->getCookie($name);
    }

    public function accepts(ContentType ...$contentType): bool
    {
        return $this->requestHolder->request->accepts(...$contentType);
    }
}
