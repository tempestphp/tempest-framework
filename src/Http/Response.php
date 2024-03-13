<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Http\Cookie\Cookie;
use Tempest\View\View;

interface Response
{
    public function getStatus(): Status;

    public function setStatus(Status $status): self;

    /** @return \Tempest\Http\Header[] */
    public function getHeaders(): array;

    public function getHeader(string $name): ?Header;

    public function addHeader(string $key, string $value): self;

    public function getBody(): string|array|null;

    public function setBody(string $body): self;

    public function getView(): ?View;

    public function setView(string|View $view, mixed ...$data): self;

    public function addSession(string $name, mixed $value): void;

    public function removeSession(string $name): void;

    public function destroySession(): void;

    public function addCookie(Cookie $cookie): void;

    public function removeCookie(string $key): void;

    public function getCookie(string $name): ?Cookie;

    /** @return Cookie[] */
    public function getCookies(): array;

    public function ok(): self;

    public function notFound(): self;

    public function redirect(string $to): self;

    public function flash(string $key, mixed $value): void;
}
