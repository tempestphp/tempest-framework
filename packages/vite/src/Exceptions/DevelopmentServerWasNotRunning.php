<?php

declare(strict_types=1);

namespace Tempest\Vite\Exceptions;

use Exception;

final class DevelopmentServerWasNotRunning extends Exception implements ViteException
{
    public function __construct()
    {
        parent::__construct('The Vite development server is not running.');
    }
}
