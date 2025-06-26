<?php

declare(strict_types=1);

namespace Tempest\Container\Exceptions;

use Exception;

final class InitializerWasInvalid extends Exception implements ContainerException
{
    public static function dynamicInitializerNotAllowed(string $initializerClassName): self
    {
        return new self(
            "Dynamic initializers are not allowed for native values, {$initializerClassName} is a dynamic initializer.",
        );
    }

    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
