<?php

declare(strict_types=1);

namespace Tempest\Console\Input;

use Exception;
use Fiber;
use FiberError;
use Tempest\Console\InputBuffer;
use Tempest\Console\Key;

final class MemoryInputBuffer implements InputBuffer
{
    private array $buffer = [];

    private ?Fiber $fiber = null;

    public function __construct() {}

    public function add(int|string|Key ...$input): void
    {
        foreach ($input as $line) {
            $this->buffer[] = $line instanceof Key
                ? $line->value
                : (string) $line;
        }

        try {
            $this->fiber?->resume();
        } catch (FiberError) {
            throw new \RuntimeException(sprintf(
                'Tried to send [%s] to the console, but no input was expected.',
                implode(
                    separator: ', ',
                    array: array_map(
                        callback: fn (int|string|Key $i) => is_string($i)
                            ? rtrim($i)
                            : $i->value,
                        array: $input,
                    ),
                ),
            ));
        }
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
            throw new Exception('No fiber running');
        }

        Fiber::suspend();

        $next = array_shift($this->buffer);

        return $next ?? '';
    }

    public function clear(): self
    {
        $this->buffer = [];

        return $this;
    }
}
