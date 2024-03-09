<?php

declare(strict_types=1);

namespace Tempest\Http;

use function Tempest\get;
use Tempest\Http\Cookie\Cookie;
use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\Session\Session;
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

    public function destroySession(): void
    {
        $this->getSession()->destroy();
    }

    public function addSession(string $name, mixed $value): void
    {
        $this->getSession()->set($name, $value);
    }

    public function removeSession(string $name): void
    {
        $this->getSession()->remove($name);
    }

    public function addCookie(Cookie $cookie): void
    {
        $this->getCookieManager()->add($cookie);
    }

    public function getCookie(string $name): ?Cookie
    {
        return $this->getCookieManager()->get($name);
    }

    public function getCookies(): array
    {
        return $this->getCookieManager()->all();
    }

    public function removeCookie(string $key): void
    {
        $this->getCookieManager()->remove($key);
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

    private function getCookieManager(): CookieManager
    {
        return get(CookieManager::class);
    }

    private function getSession(): Session
    {
        return get(Session::class);
    }
}
