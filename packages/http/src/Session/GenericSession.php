<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

use Tempest\DateTime\DateTime;
use Tempest\DateTime\DateTimeInterface;
use Tempest\Support\Str;
use UnitEnum;

final class GenericSession implements Session
{
    private array $expiredKeys = [];

    public function __construct(
        private(set) SessionId $id,
        private(set) DateTimeInterface $createdAt,
        public DateTimeInterface $lastActiveAt,
        /** @var array<array-key,mixed> */
        private(set) array $data = [],
    ) {}

    public static function unserialize(array $data): self
    {
        $session = new self(
            id: new SessionId($data['id']),
            createdAt: DateTime::fromTimestamp($data['created_at']),
            lastActiveAt: DateTime::fromTimestamp($data['last_active_at']),
            data: $data['data'],
        );

        $session->expiredKeys = $data['expired_keys'];

        return $session;
    }

    public function set(string|UnitEnum $key, mixed $value): void
    {
        $this->data[Str\parse($key)] = $value;
    }

    public function flash(string|UnitEnum $key, mixed $value): void
    {
        $this->data[Str\parse($key)] = new FlashValue($value);
    }

    public function reflash(): void
    {
        foreach ($this->data as $key => $value) {
            if (! $value instanceof FlashValue) {
                continue;
            }

            unset($this->expiredKeys[$key]);
        }
    }

    public function get(string|UnitEnum $key, mixed $default = null): mixed
    {
        $key = Str\parse($key);
        $value = $this->data[$key] ?? $default;

        if ($value instanceof FlashValue) {
            $this->expiredKeys[$key] = $key;
            $value = $value->value;
        }

        return $value;
    }

    public function consume(string|UnitEnum $key, mixed $default = null): mixed
    {
        $key = Str\parse($key);
        $value = $this->get($key, $default);

        $this->remove($key);

        return $value;
    }

    public function all(): array
    {
        return $this->data;
    }

    public function remove(string|UnitEnum $key): void
    {
        $key = Str\parse($key);

        if (isset($this->data[$key])) {
            unset($this->data[$key]);
        }
    }

    public function cleanup(): void
    {
        foreach ($this->expiredKeys as $key) {
            $this->remove($key);
        }
    }

    public function clear(): void
    {
        $this->data = [];
    }

    public function serialize(): array
    {
        return [
            'id' => (string) $this->id,
            'created_at' => $this->createdAt->getTimestamp()->getSeconds(),
            'last_active_at' => $this->lastActiveAt->getTimestamp()->getSeconds(),
            'data' => $this->data,
            'expired_keys' => $this->expiredKeys,
        ];
    }
}
