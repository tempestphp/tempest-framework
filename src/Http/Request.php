<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\Session\SessionManager;

interface Request
{
    public function getMethod(): Method;

    public function getUri(): string;

    public function getBody(): array;

    public function getHeaders(): array;

    public function getPath(): string;

    public function getQuery(): array;

    public function cookies(): CookieManager;

    public function session(): SessionManager;
}
