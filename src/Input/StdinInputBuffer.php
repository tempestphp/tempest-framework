<?php

declare(strict_types=1);

namespace Tempest\Console\Input;

use Tempest\Console\InputBuffer;

final readonly class StdinInputBuffer implements InputBuffer
{
    public function read(int $bytes): string
    {
        return fread(STDIN, $bytes);
    }

    public function readln(): string
    {
        $stream = fopen('php://stdin', 'r');

        $line = fgets($stream);

        fclose($stream);

        return $line;
    }
}
