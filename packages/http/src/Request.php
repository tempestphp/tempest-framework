<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Http\Cookie\Cookie;

interface Request
{
    public Method $method {
        get;
    }

    public string $uri {
        get;
    }

    public ?string $raw {
        get;
    }

    public array $body {
        get;
    }

    public RequestHeaders $headers {
        get;
    }

    public string $path {
        get;
    }

    public array $query {
        get;
    }

    /** @var \Tempest\Http\Upload[] $files */
    public array $files {
        get;
    }

    /** @var Cookie[] $cookies */
    public array $cookies {
        get;
    }

    public function has(string $key): bool;

    public function hasBody(string $key): bool;

    public function hasQuery(string $key): bool;

    public function get(string $key, mixed $default = null): mixed;

    public function getSessionValue(string $name): mixed;

    public function getCookie(string $name): ?Cookie;
}
