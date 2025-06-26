<?php

declare(strict_types=1);

namespace Tempest\Database\Exceptions;

use Exception;

final class ValueWasInvalid extends Exception
{
    public function __construct(string $field, string $value)
    {
        parent::__construct("Value '{$value}' provided for {$field} is not valid");
    }
}
