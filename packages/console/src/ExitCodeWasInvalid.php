<?php

declare(strict_types=1);

namespace Tempest\Console;

use Exception;

final class ExitCodeWasInvalid extends Exception implements HasExitCode
{
    public function __construct(int $original)
    {
        parent::__construct("An exit code should be between 0 and 255. Instead got {$original}");
    }

    public function getExitCode(): ExitCode
    {
        return ExitCode::INVALID_EXIT_CODE;
    }
}
