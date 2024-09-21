<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Http\Cookie\Cookie;

interface Request
{
    public function getMethod(): Method;

    public function getUri(): string;

    public function has(string $key): bool;

    public function hasBody(string $key): bool;

    public function hasQuery(string $key): bool;

    public function get(string $key, mixed $default = null): mixed;

    public function getBody(): array;

    public function getHeaders(): array;

    public function getPath(): string;

    public function getQuery(): array;

    public function getSessionValue(string $name): mixed;

    public function getCookie(string $name): ?Cookie;

    /** @return \Tempest\Http\Upload[] */
    public function getFiles(): array;

    /** @return Cookie[] */
    public function getCookies(): array;

    public function validate(): void;
}
