<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Http\Session\SessionManager;
use Tempest\View\View;

interface Response
{
    public function getStatus(): Status;

    public function getHeaders(): array;

    public function getBody(): string|array|null;

    public function getSession(): SessionManager;

    public function body(string $body): self;

    public function header(string $key, string $value): self;

    public function view(View $view): self;

    public function getView(): ?View;

    public function ok(): self;

    public function notFound(): self;

    public function redirect(string $to): self;

    public function status(Status $status): self;
}
