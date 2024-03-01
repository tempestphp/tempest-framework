<?php

declare(strict_types=1);

namespace Tempest\Clock;

use DateTimeInterface;

interface Clock
{
    public function now(): DateTimeInterface;

    public function time(): int;

    public function sleep(int $seconds): void;
}
