<?php

namespace Tempest\Auth\Exceptions;

use Exception;
use Tempest\Auth\Authentication\Authenticatable;

final class ModelIsNotAuthenticatable extends Exception implements AuthenticationException
{
    public function __construct(
        private readonly string $class,
    ) {
        parent::__construct(
            sprintf('`%s` must be an instance of `%s`', $class, Authenticatable::class),
        );
    }
}
