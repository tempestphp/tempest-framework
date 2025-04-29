<?php

declare(strict_types=1);

namespace Tempest\Console;

interface OutputBuffer
{
    public function write(string $contents): void;
}
