<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

use const PHP_SESSION_ACTIVE;
use RuntimeException;
use SessionHandlerInterface;
use Tempest\Support\ArrayHelper;

final class Session
{
    private bool $isStarted = false;

    // TODO: allow the passing of options
    public function __construct(private SessionHandlerInterface $sessionHandler)
    {
        // Here we just make sure that if the application ever
        // prematurely stops (e.g., we call exit or die),
        // the session data will still be saved.
        session_register_shutdown();

        $this->setSessionHandler($this->sessionHandler);
    }

    public function getId(): string
    {
        return session_id();
    }

    public function setId(string $id): void
    {
        if (session_id($id) === false) {
            throw new RuntimeException('Failed to set the session id.');
        }
    }

    public function getName(): string
    {
        return session_name();
    }

    public function setName(string $name): void
    {
        if (session_name($name) === false) {
            throw new RuntimeException('Failed to set the session name.');
        }
    }

    public function getSessionHandler(): SessionHandlerInterface
    {
        return $this->sessionHandler;
    }

    public function setSessionHandler(SessionHandlerInterface $sessionHandler): void
    {
        if (session_status() === PHP_SESSION_ACTIVE || headers_sent()) {
            return;
        }

        session_set_save_handler($sessionHandler, false);
    }

    public function isStarted(): bool
    {
        return $this->isStarted;
    }

    public function start(): void
    {
        if ($this->isStarted()) {
            return;
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            throw new RuntimeException('The session has already been started by PHP.');
        }

        if (session_id() === false) {
            $this->setId(session_create_id());
        }

        if (session_start() === false) {
            throw new RuntimeException('Failed to start the session.');
        }

        $this->isStarted = true;
    }

    public function has(string $key): bool
    {
        return ArrayHelper::has($_SESSION, $key);
    }

    public function missing(string $key): bool
    {
        return ! $this->has($key);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        // We use an array helper here, so we can
        // support dot notation.
        return ArrayHelper::get($_SESSION, $key, $default);
    }

    public function set(string $key, mixed $value): void
    {
        // We use an array helper here, so we can
        // support dot notation.
        ArrayHelper::set($_SESSION, $key, $value);
    }

    public function all(): array
    {
        return $_SESSION;
    }

    public function save(): void
    {
        session_write_close();
    }
}
