<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Http\Cookie\Cookie;
use Tempest\View\View;

interface Response
{
    public function getStatus(): Status;

    public function getHeaders(): array;

    public function getBody(): string|array|null;

    public function body(string $body): self;

    public function header(string $key, string $value): self;

    public function view(View $view): self;

    public function getView(): ?View;

    public function destroySession(): void;

    public function addSession(string $name, mixed $value): void;

    public function removeSession(string $name): void;

    public function addCookie(Cookie $cookie): void;

    public function getCookie(string $name): ?Cookie;

    /** @return Cookie[] */
    public function getCookies(): array;

    public function removeCookie(string $key): void;

    public function ok(): self;

    public function notFound(): self;

    public function redirect(string $to): self;

    public function status(Status $status): self;
}
