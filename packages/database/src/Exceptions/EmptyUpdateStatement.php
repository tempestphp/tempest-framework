<?php

namespace Tempest\Database\Exceptions;

use Exception;

final class EmptyUpdateStatement extends Exception
{
    public function __construct()
    {
        parent::__construct('Cannot build an update statement without data.');
    }
}
