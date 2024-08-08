<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Http\Cookie\Cookie;

interface Request
{
    public function getMethod(): Method;

    public function getUri(): string;

    public function get(string $key): mixed;

    public function getBody(): array;

    public function getHeaders(): array;

    public function getPath(): string;

    public function getQuery(): array;

    public function getSessionValue(string $name): mixed;

    public function getCookie(string $name): ?Cookie;

    /** @return Cookie[] */
    public function getCookies(): array;

    public function validate(): void;
}
