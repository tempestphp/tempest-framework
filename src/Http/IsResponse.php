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
    /** @var \Tempest\Http\Header[] */
    private array $headers = [];
    private ?View $view = null;

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $name): ?Header
    {
        return $this->headers[$name];
    }

    public function addHeader(string $key, string $value): self
    {
        $this->headers[$key] ??= new Header($key);

        $this->headers[$key]->add($value);

        return $this;
    }

    public function getBody(): string|array|null
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getView(): ?View
    {
        return $this->view;
    }

    public function setView(string|View $view, mixed ...$data): self
    {
        if (is_string($view)) {
            $view = view($view)->data(...$data);
        }

        $this->view = $view;

        return $this;
    }

    public function addSession(string $name, mixed $value): void
    {
        $this->getSession()->set($name, $value);
    }

    public function removeSession(string $name): void
    {
        $this->getSession()->remove($name);
    }

    public function destroySession(): void
    {
        $this->getSession()->destroy();
    }

    public function addCookie(Cookie $cookie): void
    {
        $this->getCookieManager()->add($cookie);
    }

    public function removeCookie(string $key): void
    {
        $this->getCookieManager()->remove($key);
    }

    public function getCookie(string $name): ?Cookie
    {
        return $this->getCookieManager()->get($name);
    }

    public function getCookies(): array
    {
        return $this->getCookieManager()->all();
    }

    public function flash(string $key, mixed $value): void
    {
        $this->getSession()->flash($key, $value);
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

    public function redirect(string $to): self
    {
        return $this
            ->addHeader('Location', $to)
            ->setStatus(Status::FOUND);
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
