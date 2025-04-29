<?php

declare(strict_types=1);

namespace Tempest\Console;

interface HasExitCode
{
    public function getExitCode(): ExitCode;
}
