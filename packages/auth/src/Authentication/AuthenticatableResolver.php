<?php

namespace Tempest\Auth\Authentication;

interface AuthenticatableResolver
{
    /**
     * Resolves an authenticatable entity by the given ID.
     *
     * @param class-string<CanAuthenticate> $class
     */
    public function resolve(int|string $id, string $class): ?CanAuthenticate;

    /**
     * Resolves an identifier for the given authenticatable entity.
     */
    public function resolveId(CanAuthenticate $authenticatable): int|string;
}
