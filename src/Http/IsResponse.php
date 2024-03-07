<?php

declare(strict_types=1);

namespace Tempest\Http;

use function Tempest\get;
use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\Session\SessionManager;
use function Tempest\view;
use Tempest\View\View;

trait IsResponse
{
    private Status $status;
    private string|array|null $body = null;
    /** @var array @var string[][] */
    private array $headers = [];
    private ?View $view = null;

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function getBody(): string|array|null
    {
        return $this->body;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function session(): SessionManager
    {
        return get(SessionManager::class);
    }

    public function cookies(): CookieManager
    {
        return get(CookieManager::class);
    }

    public function header(string $key, string $value): self
    {
        $this->headers[$key][] = $value;

        return $this;
    }

    public function body(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function view(string|View $view, mixed ...$data): self
    {
        if (is_string($view)) {
            $view = view($view)->data(...$data);
        }

        $this->view = $view;

        return $this;
    }

    public function getView(): ?View
    {
        return $this->view;
    }

    public function ok(): self
    {
        $this->status = Status::OK;

        return $this;
    }

    public function notFound(): self
    {
        $this->status = Status::NOT_FOUND;

        return $this;
    }

    public function status(Status $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function redirect(string $to): self
    {
        return $this
            ->header('Location', $to)
            ->status(Status::FOUND);
    }
}
