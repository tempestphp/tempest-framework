<?php

declare(strict_types=1);

namespace Tempest\Console;

use Exception;

final class InvalidExitCode extends Exception
{
    public function __construct(int $original)
    {
        parent::__construct("An exit code should be between 0 and 255. Instead got {$original}");
    }
}
