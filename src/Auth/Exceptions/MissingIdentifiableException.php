<?php

declare(strict_types=1);

namespace Tempest\Auth\Exceptions;

use Exception;

final class MissingIdentifiableException extends Exception
{
    public function __construct()
    {
        parent::__construct('The identifiable is not set in the config file.');
    }
}
