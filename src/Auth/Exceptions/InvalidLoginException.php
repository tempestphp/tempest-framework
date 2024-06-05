<?php

declare(strict_types=1);

namespace Tempest\Auth\Exceptions;

use Exception;

final class InvalidLoginException extends Exception
{
    public function __construct()
    {
        parent::__construct('The provided credentials are invalid.');
    }
}
