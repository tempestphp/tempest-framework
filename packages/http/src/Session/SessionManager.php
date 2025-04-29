<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

interface SessionManager
{
    public function create(SessionId $id): Session;

    public function set(SessionId $id, string $key, mixed $value): void;

    public function get(SessionId $id, string $key, mixed $default = null): mixed;

    public function all(SessionId $id): array;

    public function remove(SessionId $id, string $key): void;

    public function destroy(SessionId $id): void;

    public function isValid(SessionId $id): bool;

    public function cleanup(): void;
}
