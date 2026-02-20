<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

use Tempest\DateTime\DateTimeInterface;
use UnitEnum;

/**
 * Represents the current session.
 *
 * @see ManageSessionMiddleware
 * @see SessionManager
 */
interface Session
{
    public SessionId $id {
        get;
    }

    public DateTimeInterface $createdAt {
        get;
    }

    public DateTimeInterface $lastActiveAt {
        get;
        set;
    }

    /** @var array<array-key,mixed> */
    public array $data {
        get;
    }

    /**
     * Sets a value in the session.
     */
    public function set(string|UnitEnum $key, mixed $value): void;

    /**
     * Stores a value in the session that will be available for the next request only.
     */
    public function flash(string|UnitEnum $key, mixed $value): void;

    /**
     * Reflashes all flash values in the session, making them available for the next request.
     */
    public function reflash(): void;

    /**
     * Retrieves a value from the session.
     */
    public function get(string|UnitEnum $key, mixed $default = null): mixed;

    /**
     * Retrieves the value for the given key and removes it from the session.
     */
    public function consume(string|UnitEnum $key, mixed $default = null): mixed;

    /**
     * Retrieves all values from the session.
     */
    public function all(): array;

    /**
     * Removes a value from the session.
     */
    public function remove(string|UnitEnum $key): void;

    /**
     * Cleans up expired session values.
     */
    public function cleanup(): void;

    /**
     * Clears all values from the session.
     */
    public function clear(): void;

    public function serialize(): array;
}
