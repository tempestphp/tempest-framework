<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

use DateTimeImmutable;
use function Tempest\get;

final class Session
{
    public const string ID = 'tempest_session_id';

    public function __construct(
        public SessionId $id,
        public DateTimeImmutable $createdAt,
        public array $data = [],
    ) {
    }

    public function put(string $key, mixed $value): void
    {
        $this->getSessionManager()->put($this->id, $key, $value);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->getSessionManager()->get($this->id, $key, $default);
    }

    public function remove(string $key): void
    {
        $this->getSessionManager()->remove($this->id, $key);
    }

    public function destroy(): void
    {
        $this->getSessionManager()->destroy($this->id);
    }

    public function isValid(): bool
    {
        return $this->getSessionManager()->isValid($this->id);
    }

    private function getSessionManager(): SessionManager
    {
        return get(SessionManager::class);
    }
}
