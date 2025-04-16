<?php

namespace Tests\Tempest\Integration\DateTime\Fixtures;

use Tempest\DateTime\DateTimeInterface;

final class ClassUsingDateTime
{
    public function __construct(
        public DateTimeInterface $now,
    ) {}
}
