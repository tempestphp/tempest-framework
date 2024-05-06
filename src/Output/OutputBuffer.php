<?php

declare(strict_types=1);

namespace Tempest\Console\Output;

interface OutputBuffer
{
    public function write(string $contents): void;
}
