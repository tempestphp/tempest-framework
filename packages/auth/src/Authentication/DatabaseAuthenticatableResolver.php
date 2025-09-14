<?php

namespace Tempest\Auth\Authentication;

use Tempest\Auth\Exceptions\AuthenticatableModelWasInvalid;
use Tempest\Auth\Exceptions\ModelIsNotAuthenticatable;
use Tempest\Database\Database;

use function Tempest\Database\inspect;
use function Tempest\Database\query;

final readonly class DatabaseAuthenticatableResolver implements AuthenticatableResolver
{
    public function __construct(
        private Database $database,
    ) {}

    public function resolve(int|string $id, string $class): ?Authenticatable
    {
        if (! is_a($class, Authenticatable::class, allow_string: true)) {
            throw new ModelIsNotAuthenticatable($class);
        }

        return query($class)->findById($id);
    }

    public function resolveId(Authenticatable $authenticatable): int|string
    {
        $inspector = inspect($authenticatable);

        if (! $inspector->hasPrimaryKey()) {
            throw AuthenticatableModelWasInvalid::didNotHavePrimaryKey($authenticatable::class);
        }

        $id = $inspector->getPrimaryKeyValue()?->value;

        if ($id === null) {
            throw AuthenticatableModelWasInvalid::primaryKeyWasNotInitialized($authenticatable::class);
        }

        return $id;
    }
}
