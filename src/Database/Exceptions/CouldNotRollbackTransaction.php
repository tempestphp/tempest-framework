<?php

declare(strict_types=1);

namespace Tempest\Database\Exceptions;

final class CouldNotRollbackTransaction extends DatabaseException
{
    public function __construct()
    {
        parent::__construct('Cannot rollback transaction');
    }
}
