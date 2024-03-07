<?php

namespace Tempest\Http\Session;

interface SessionManager
{
    public function create(SessionId $id): Session;

    public function put(SessionId $id, string $key, mixed $value): void;

    public function get(SessionId $id, string $key, mixed $default = null): mixed;

    public function remove(SessionId $id, string $key): void;

    public function destroy(SessionId $id): void;

    public function isValid(SessionId $id): bool;

    public function cleanup(): void;
}