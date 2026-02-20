<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

use Tempest\Container\Singleton;
use Tempest\DateTime\DateTimeInterface;
use UnitEnum;

#[Singleton]
class OpaqueSession implements Session
{
    public SessionId $id {
        get {
            return $this->getSession()->id;
        }
    }

    public DateTimeInterface $createdAt {
        get {
            return $this->getSession()->createdAt;
        }
    }

    public DateTimeInterface $lastActiveAt {
        get {
            return $this->getSession()->lastActiveAt;
        }
        set {
            $this->getSession()->lastActiveAt = $value;
        }
    }

    public array $data {
        get {
            return $this->getSession()->data;
        }
    }

    public function __construct(
        private SessionIdResolver $sessionIdResolver,
        private SessionManager $sessionManager,
    ) {}

    public function set(string|UnitEnum $key, mixed $value): void
    {
        $this->getSession()->set($key, $value);
    }

    public function flash(string|UnitEnum $key, mixed $value): void
    {
        $this->getSession()->flash($key, $value);
    }

    public function reflash(): void
    {
        $this->getSession()->reflash();
    }

    public function get(string|UnitEnum $key, mixed $default = null): mixed
    {
        return $this->getSession()->get($key, $default);
    }

    public function consume(string|UnitEnum $key, mixed $default = null): mixed
    {
        return $this->getSession()->consume($key, $default);
    }

    public function all(): array
    {
        return $this->getSession()->data;
    }

    public function remove(string|UnitEnum $key): void
    {
        $this->getSession()->remove($key);
    }

    public function cleanup(): void
    {
        $this->getSession()->cleanup();
    }

    public function clear(): void
    {
        $this->getSession()->clear();
    }

    public function serialize(): array
    {
        return $this->getSession()->serialize();
    }

    private function getSession(): Session
    {
        $sessionId = $this->sessionIdResolver->resolve();

        return $this->sessionManager->getOrCreate($sessionId);
    }
}
