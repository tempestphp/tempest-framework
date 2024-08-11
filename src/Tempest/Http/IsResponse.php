<?php

declare(strict_types=1);

namespace Tempest\Http;

use Generator;
use function Tempest\get;
use Tempest\Http\Cookie\Cookie;
use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\Session\Session;
use Tempest\View\View;

trait IsResponse
{
    private Status $status = Status::OK;

    private View|string|array|Generator|null $body = null;

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
        return $this->headers[$name] ?? null;
    }

    public function addHeader(string $key, string $value): self
    {
        $this->headers[$key] ??= new Header($key);

        $this->headers[$key]->add($value);

        return $this;
    }

    public function removeHeader(string $key): self
    {
        unset($this->headers[$key]);

        return $this;
    }

    public function getBody(): View|string|array|Generator|null
    {
        return $this->body;
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

    public function setContentType(ContentType $contentType): self
    {
        $this
            ->removeHeader(ContentType::HEADER)
            ->addHeader(ContentType::HEADER, $contentType->value);

        return $this;
    }

    public function setStatus(Status $status): self
    {
        $this->status = $status;

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
