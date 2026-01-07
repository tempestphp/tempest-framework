<?php

declare(strict_types=1);

namespace Tempest\Http;

use Generator;
use JsonSerializable;
use Tempest\Http\Cookie\Cookie;
use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\Session\Session;
use Tempest\View\View;
use UnitEnum;

use function Tempest\get;

/** @phpstan-require-implements \Tempest\Http\Response */
trait IsResponse
{
    private(set) Status $status = Status::OK;

    private(set) View|string|array|Generator|JsonSerializable|null $body = null;

    /** @var \Tempest\Http\Header[] */
    private(set) array $headers = [];

    public CookieManager $cookieManager {
        get => get(CookieManager::class);
    }

    public Session $session {
        get => get(Session::class);
    }

    private(set) ?View $view = null;

    public function getHeader(string $name): ?Header
    {
        return array_find(
            array: $this->headers,
            callback: fn (Header $header) => strcasecmp($header->name, $name) === 0,
        );
    }

    public function addHeaders(array $headers): self
    {
        foreach ($headers as $key => $values) {
            if (! is_array($values)) {
                $values = [$values];
            }

            foreach ($values as $value) {
                $this->addHeader($key, $value);
            }
        }

        return $this;
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

    public function flash(string|UnitEnum $key, mixed $value): self
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
