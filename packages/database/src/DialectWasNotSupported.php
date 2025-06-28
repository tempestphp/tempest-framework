<?php

declare(strict_types=1);

namespace Tempest\Database;

use Exception;

final class DialectWasNotSupported extends Exception
{
    public function __construct()
    {
        parent::__construct('Unsupported dialect');
    }
}
