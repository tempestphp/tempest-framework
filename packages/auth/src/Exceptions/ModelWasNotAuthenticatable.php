<?php

namespace Tempest\Auth\Exceptions;

use Exception;
use Tempest\Auth\Authentication\Authenticatable;

final class ModelWasNotAuthenticatable extends Exception implements AuthenticationException
{
    public function __construct(
        string $class,
    ) {
        parent::__construct(
            sprintf('`%s` must be an instance of `%s`', $class, Authenticatable::class),
        );
    }
}
