<?php

declare(strict_types=1);

namespace Tempest\Database\Exceptions;

use Exception;

final class CouldNotRollbackTransaction extends Exception implements DatabaseException
{
    public function __construct()
    {
        parent::__construct('Cannot rollback transaction');
    }
}
