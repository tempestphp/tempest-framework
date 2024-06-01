<?php

declare(strict_types=1);

namespace Tempest\Console\Input;

use Exception;
use Fiber;
use Tempest\Console\InputBuffer;
use Tempest\Console\Key;

final class MemoryInputBuffer implements InputBuffer
{
    private array $buffer = [];
    private ?Fiber $fiber = null;

    public function __construct()
    {
    }

    public function add(int|string|Key ...$input): void
    {
        foreach ($input as $line) {
            $this->buffer[] = $line instanceof Key
                ? $line->value
                : (string) $line;
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

        $next = array_shift($this->buffer);

        return $next ?? '';
    }
}
