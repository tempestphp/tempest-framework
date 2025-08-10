<?php

namespace Tempest\Auth\Exceptions;

use Exception;
use Tempest\Auth\Authentication\CanAuthenticate;

final class ModelIsNotAuthenticatable extends Exception implements AuthenticationException
{
    public function __construct(
        private readonly string $class,
    ) {
        parent::__construct(
            sprintf('`%s` must be an instance of `%s`', $class, CanAuthenticate::class),
        );
    }
}
