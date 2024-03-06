<?php

namespace Tempest\Http\Session;

interface Session
{
    public function create(): void;

    public function put(string $key, mixed $value): void;

    public function get(string $key, mixed $default = null): mixed;

    public function remove(string $key): void;

    public function destroy(): void;

    public function isValid(): bool;
}