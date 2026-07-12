<?php

namespace Tempest\Database\Exceptions;

use Exception;

final class CouldNotResetConnection extends Exception implements DatabaseException
{
    public function __construct(string $message = '')
    {
        parent::__construct($message);
    }
}
