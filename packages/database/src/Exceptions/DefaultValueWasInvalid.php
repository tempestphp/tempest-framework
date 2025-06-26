<?php

declare(strict_types=1);

namespace Tempest\Database\Exceptions;

use Exception;

final class DefaultValueWasInvalid extends Exception
{
    public function __construct(string $field, string $value)
    {
        parent::__construct("Default value '{$value}' provided for {$field} is not valid");
    }
}
