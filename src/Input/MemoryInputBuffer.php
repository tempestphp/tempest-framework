<?php

declare(strict_types=1);

namespace Tempest\Console\Input;

use Exception;
use Fiber;
use Tempest\Console\Key;

final class MemoryInputBuffer implements InputBuffer
{
    private ?string $buffer = null;
    private ?Fiber $fiber = null;

    public function __construct() {}

    public function add(string|Key ...$input): void
    {
        foreach ($input as $line) {
            $this->buffer .= $line instanceof Key
                ? $line->value
                : $line;
        }

        $this->fiber->resume();
    }

    public function read(int $bytes): string
    {
        return $this->consumeBuffer();
    }

    public function readln(): string
    {
        return $this->consumeBuffer();
    }

    private function consumeBuffer(): string
    {
        $this->fiber = Fiber::getCurrent();

        if (! $this->fiber?->isRunning()) {
            throw new Exception("No fiber running");
        }

        Fiber::suspend();

        $buffer = $this->buffer;

        if ($buffer === null) {
            throw new Exception('Empty buffer');
        }
        
        $this->buffer = null;

        return $buffer;
    }
}
