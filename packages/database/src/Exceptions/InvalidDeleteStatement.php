<?php

namespace Tempest\Database\Exceptions;

use Exception;

final class InvalidDeleteStatement extends Exception
{
    public function __construct()
    {
        parent::__construct('Cannot build a delete statement without either a condition or explicit allowance to delete everything.');
    }
}
