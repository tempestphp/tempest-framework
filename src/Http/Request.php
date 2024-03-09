<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\Session\Session;

interface Request
{
    public function getMethod(): Method;

    public function getUri(): string;

    public function getBody(): array;

    public function getHeaders(): array;

    public function getPath(): string;

    public function getQuery(): array;

    public function getSession(): Session;

    public function getCookies(): CookieManager;
}
