<?php

namespace Tempest\Auth\Authentication;

use Tempest\Auth\AuthConfig;
use Tempest\Auth\Exceptions\AuthenticatableModelWasInvalid;
use Tempest\Auth\Exceptions\ModelIsNotAuthenticatable;
use Tempest\Database\Database;

use function Tempest\Database\inspect;
use function Tempest\Database\query;

final readonly class DatabaseAuthenticatableResolver implements AuthenticatableResolver
{
    public function __construct(
        private AuthConfig $authConfig,
        private Database $database,
    ) {}

    public function resolve(int|string $id): ?CanAuthenticate
    {
        $model = query($this->authConfig->authenticatable)->findById($id);

        if (! ($model instanceof CanAuthenticate)) {
            throw new ModelIsNotAuthenticatable($this->authConfig->authenticatable);
        }

        return $model;
    }

    public function resolveId(CanAuthenticate $authenticatable): int|string
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
