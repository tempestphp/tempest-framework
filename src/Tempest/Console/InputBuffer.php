<?php

declare(strict_types=1);

namespace Tempest\Console;

interface InputBuffer
{
    public function read(int $bytes): string;

    public function readln(): string;
}
