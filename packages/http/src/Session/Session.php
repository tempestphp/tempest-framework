<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

use Tempest\DateTime\DateTime;
use Tempest\DateTime\DateTimeInterface;
use Tempest\Support\Str;
use UnitEnum;

/**
 * Represents the current session.
 *
 * @see ManageSessionMiddleware
 * @see SessionManager
 */
final class Session
{
    /**
     * Stores the keys for session values that have expired.
     */
    private array $expiredKeys = [];

    public function __construct(
        private(set) SessionId $id,
        private(set) DateTimeInterface $createdAt,
        public DateTimeInterface $lastActiveAt,
        /** @var array<array-key,mixed> */
        private(set) array $data = [],
    ) {}

    /**
     * Sets a value in the session.
     */
    public function set(string|UnitEnum $key, mixed $value): void
    {
        $this->data[Str\parse($key)] = $value;
    }

    /**
     * Stores a value in the session that will be available for the next request only.
     */
    public function flash(string|UnitEnum $key, mixed $value): void
    {
        $this->data[Str\parse($key)] = new FlashValue($value);
    }

    /**
     * Reflashes all flash values in the session, making them available for the next request.
     */
    public function reflash(): void
    {
        foreach ($this->data as $key => $value) {
            if (! $value instanceof FlashValue) {
                continue;
            }

            unset($this->expiredKeys[$key]);
        }
    }

    /**
     * Retrieves a value from the session.
     */
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

    /**
     * Retrieves the value for the given key and removes it from the session.
     */
    public function consume(string|UnitEnum $key, mixed $default = null): mixed
    {
        $key = Str\parse($key);
        $value = $this->get($key, $default);

        $this->remove($key);

        return $value;
    }

    /**
     * Retrieves all values from the session.
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Removes a value from the session.
     */
    public function remove(string|UnitEnum $key): void
    {
        $key = Str\parse($key);

        if (isset($this->data[$key])) {
            unset($this->data[$key]);
        }
    }

    /**
     * Cleans up expired session values.
     */
    public function cleanup(): void
    {
        foreach ($this->expiredKeys as $key) {
            $this->remove($key);
        }
    }

    /**
     * Clears all values from the session.
     */
    public function clear(): void
    {
        $this->data = [];
    }

    public function __serialize(): array
    {
        return [
            'id' => (string) $this->id,
            'created_at' => $this->createdAt->getTimestamp()->getSeconds(),
            'last_active_at' => $this->lastActiveAt->getTimestamp()->getSeconds(),
            'data' => $this->data,
            'expired_keys' => $this->expiredKeys,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id = new SessionId($data['id']);
        $this->createdAt = DateTime::fromTimestamp($data['created_at']);
        $this->lastActiveAt = DateTime::fromTimestamp($data['last_active_at']);
        $this->data = $data['data'];
        $this->expiredKeys = $data['expired_keys'];
    }
}
