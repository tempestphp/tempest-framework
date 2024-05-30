<?php

declare(strict_types=1);

namespace Tempest\Database\Exceptions;

final class CannotCommitTransaction extends DatabaseException
{
    public function __construct()
    {
        parent::__construct('Cannot commit transaction');
    }
}
