<?php

namespace Tempest\Database\Exceptions;

use Exception;

final class InvalidUpdateStatement extends Exception
{
    public function __construct()
    {
        parent::__construct('Cannot build a update statement without either a condition or explicit allowance to update everything.');
    }
}
