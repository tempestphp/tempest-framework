<?php

declare(strict_types=1);

namespace Tempest\Router;

use Generator;
use Tempest\Http\Status;
use Tempest\Router\Cookie\Cookie;
use Tempest\Router\Cookie\CookieManager;
use Tempest\Router\Session\Session;
use Tempest\View\View;

use function Tempest\get;

/** @phpstan-require-implements \Tempest\Router\Response */
trait IsResponse
{
    private(set) Status $status = Status::OK;

    private(set) View|string|array|Generator|null $body = null;

    /** @var \Tempest\Router\Header[] */
    private(set) array $headers = [];

    public Session $session {
        get => get(Session::class);
    }

    public CookieManager $cookieManager {
        get => get(CookieManager::class);
    }

    private(set) ?View $view = null;

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

    public function addSession(string $name, mixed $value): self
    {
        $this->session->set($name, $value);

        return $this;
    }

    public function removeSession(string $name): self
    {
        $this->session->remove($name);

        return $this;
    }

    public function destroySession(): self
    {
        $this->session->destroy();

        return $this;
    }

    public function addCookie(Cookie $cookie): self
    {
        $this->cookieManager->add($cookie);

        return $this;
    }

    public function removeCookie(string $key): self
    {
        $this->cookieManager->remove($key);

        return $this;
    }

    public function flash(string $key, mixed $value): self
    {
        $this->session->flash($key, $value);

        return $this;
    }

    public function setContentType(ContentType $contentType): self
    {
        $this->removeHeader(ContentType::HEADER)
            ->addHeader(ContentType::HEADER, $contentType->value);

        return $this;
    }

    public function setStatus(Status $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function setBody(View|string|array|Generator|null $body): self
    {
        $this->body = $body;

        return $this;
    }
}
