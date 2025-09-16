<?php

namespace Tempest\Auth\Authentication;

interface AuthenticatableResolver
{
    /**
     * Resolves an authenticatable entity by the given ID.
     *
     * @param class-string<Authenticatable> $class
     */
    public function resolve(int|string $id, string $class): ?Authenticatable;

    /**
     * Resolves an identifier for the given authenticatable entity.
     */
    public function resolveId(Authenticatable $authenticatable): int|string;
}
