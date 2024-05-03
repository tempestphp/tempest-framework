<?php

declare(strict_types=1);

namespace Tempest\Console\Input;

interface InputBuffer
{
    public function read(int $bytes): string;

    public function readln(): string;
}
