<?php

namespace Tempest\Mapper\Exceptions;

use Exception;

final class CannotSerializeValue extends Exception
{
    public function __construct(string $expectedType)
    {
        parent::__construct('Could not serialize value to ' . $expectedType);
    }
}