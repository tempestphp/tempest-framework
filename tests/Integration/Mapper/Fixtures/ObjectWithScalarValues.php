<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Mapper\Strict;

#[Strict]
final class ObjectWithScalarValues
{
    public function __construct(
        public bool $active,
        public float $score,
        public int $count,
    ) {}
}
