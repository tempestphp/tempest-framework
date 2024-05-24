<?php

declare(strict_types=1);

namespace Tempest\Http;

use function Tempest\get;
use Tempest\Http\Cookie\Cookie;
use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\Session\Session;
use Tempest\View\View;

trait IsResponse
{
    private Status $status;
    private View|string|array|null $body = null;
    /** @var \Tempest\Http\Header[] */
    private array $headers = [];
    private ?View $view = null;

    public function getStatus(): Status
    {
        return $this->status;
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

    public function getBody(): View|string|array|null
    {
        return $this->body;
    }

    public function setBody(View|string|array|null $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function addSession(string $name, mixed $value): self
    {
        $this->getSession()->set($name, $value);

        return $this;
    }

    public function removeSession(string $name): self
    {
        $this->getSession()->remove($name);

        return $this;
    }

    public function destroySession(): self
    {
        $this->getSession()->destroy();

        return $this;
    }

    public function addCookie(Cookie $cookie): self
    {
        $this->getCookieManager()->add($cookie);

        return $this;
    }

    public function removeCookie(string $key): self
    {
        $this->getCookieManager()->remove($key);

        return $this;
    }

    public function flash(string $key, mixed $value): self
    {
        $this->getSession()->flash($key, $value);

        return $this;
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
