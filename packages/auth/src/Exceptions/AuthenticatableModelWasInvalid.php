<?php

namespace Tempest\Auth\Exceptions;

use Exception;
use Tempest\Auth\Authentication\Authenticatable;

final class AuthenticatableModelWasInvalid extends Exception implements AuthenticationException
{
    /**
     * @param class-string<Authenticatable> $class
     */
    public static function didNotHavePrimaryKey(string $class): self
    {
        return new self(sprintf('`%s` has no primary key defined. Please ensure the class implements the `Authenticatable` interface and defines a primary key.', $class));
    }

    /**
     * @param class-string<Authenticatable> $class
     */
    public static function primaryKeyWasNotInitialized(string $class): self
    {
        return new self(sprintf("`%s`'s primary key is not initialized.", $class));
    }
}
