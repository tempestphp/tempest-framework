<?php

declare(strict_types=1);

namespace Tempest\Http;

use Generator;
use JsonSerializable;
use Tempest\Http\Cookie\Cookie;
use Tempest\View\View;
use UnitEnum;

interface Response
{
    /**
     * Gets the status code of the response.
     */
    public Status $status {
        get;
    }

    /**
     * Gets the headers of the response.
     *
     * @var \Tempest\Http\Header[] $headers
     */
    public array $headers {
        get;
    }

    /**
     * Gets the body of the response.
     */
    public View|string|array|Generator|JsonSerializable|null $body {
        get;
    }

    /**
     * Gets a header by its name, case insensitive.
     */
    public function getHeader(string $name): ?Header;

    /**
     * Adds a header to the response.
     */
    public function addHeader(string $key, string $value): self;

    /**
     * Removes a header from the response.
     */
    public function removeHeader(string $key): self;

    /**
     * Adds a value to the session.
     */
    public function addSession(string $name, mixed $value): self;

    /**
     * Removes a value from the session.
     */
    public function removeSession(string $name): self;

    /**
     * Flash a value to the session for the next request.
     */
    public function flash(string|UnitEnum $key, mixed $value): self;

    /**
     * Adds a cookie to the response.
     */
    public function addCookie(Cookie $cookie): self;

    /**
     * Removes a cookie from the response.
     */
    public function removeCookie(string $key): self;

    /**
     * Sets the status code of the response.
     */
    public function setStatus(Status $status): self;

    /**
     * Sets the body of the response.
     */
    public function setBody(View|string|array|Generator|null $body): self;
}
