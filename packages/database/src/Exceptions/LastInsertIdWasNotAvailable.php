<?php

namespace Tempest\Database\Exceptions;

use Exception;

final class LastInsertIdWasNotAvailable extends Exception
{
    public function __construct()
    {
        parent::__construct('No last insert id available.');
    }
}
