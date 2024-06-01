<?php

declare(strict_types=1);

namespace Tempest\Console\Input;

use Exception;
use Tempest\Console\InputBuffer;

final readonly class UnsupportedInputBuffer implements InputBuffer
{
    public function read(int $bytes): string
    {
        throw new Exception('Unsupported');
    }

    public function readln(): string
    {
        throw new Exception('Unsupported');
    }
}
