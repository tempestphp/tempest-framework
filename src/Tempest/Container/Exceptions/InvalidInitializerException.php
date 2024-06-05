<?php

declare(strict_types=1);

namespace Tempest\Container\Exceptions;

use Exception;

final class InvalidInitializerException extends Exception
{
    public function __construct(string $initializerClassName)
    {
        parent::__construct("Initializers must be implement Initializer, {$initializerClassName} does not.");
    }
}
