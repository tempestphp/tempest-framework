<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

use DateTimeImmutable;
use function Tempest\get;

final class Session
{
    public const string ID = 'tempest_session_id';
    public const string VALIDATION_ERRORS = 'validation_errors';
    public const string ORIGINAL_VALUES = 'original_values';

    public function __construct(
        public SessionId $id,
        public DateTimeImmutable $createdAt,
        /** @var array<array-key, mixed> */
        public array $data = [],
    ) {
    }

    public function set(string $key, mixed $value): void
    {
        $this->getSessionManager()->set($this->id, $key, $value);
    }

    public function flash(string $key, mixed $value): void
    {
        $this->getSessionManager()->set($this->id, $key, new FlashValue($value));
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->getSessionManager()->get($this->id, $key, $default);

        if ($value instanceof FlashValue) {
            $value = $value->value;
            $this->getSessionManager()->remove($this->id, $key);
        }

        return $value;
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
