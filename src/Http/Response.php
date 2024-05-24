<?php

declare(strict_types=1);

namespace Tempest\Http;

use Generator;
use Tempest\Http\Cookie\Cookie;
use Tempest\View\View;

interface Response
{
    public function getStatus(): Status;

    /** @return \Tempest\Http\Header[] */
    public function getHeaders(): array;

    public function getHeader(string $name): ?Header;

    public function addHeader(string $key, string $value): self;

    public function removeHeader(string $key): self;

    public function getBody(): View|string|array|Generator|null;

    public function addSession(string $name, mixed $value): self;

    public function flash(string $key, mixed $value): self;

    public function removeSession(string $name): self;

    public function destroySession(): self;

    public function addCookie(Cookie $cookie): self;

    public function removeCookie(string $key): self;
}
