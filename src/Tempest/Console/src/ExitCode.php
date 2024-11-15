<?php

declare(strict_types=1);

namespace Tempest\Console;

final readonly class ExitCode
{
    public function __construct(
        public int $code,
    ) {
        if ($this->code < 0 || $this->code > 255) {
            throw new InvalidExitCode($this->code);
        }
    }

    public function equals(self $other): bool
    {
        return $this->code === $other->code;
    }

    public static function success(): self
    {
        return new self(0);
    }

    public static function error(): self
    {
        return new self(1);
    }

    public static function invalid(): self
    {
        return new self(2);
    }

    public static function cancelled(): self
    {
        return new self(25);
    }
}
