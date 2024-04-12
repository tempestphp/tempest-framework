<?php

declare(strict_types=1);

namespace Tempest\Console;

interface ConsoleInput
{
    public function readln(): string;

    public function read(int $bytes): string;
}
