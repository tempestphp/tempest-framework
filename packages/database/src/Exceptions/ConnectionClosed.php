<?php

declare(strict_types=1);

namespace Tempest\Database\Exceptions;

final class ConnectionClosed extends DatabaseOperationFailed
{
    public function __construct()
    {
        parent::__construct('Connection is closed');
    }
}
