<?php

declare(strict_types=1);

namespace Tempest\Mapper\Tests\Integration\Fixtures;

use DateTimeImmutable;
use Tempest\Mapper\Strict;

#[Strict]
final class ObjectWithNullableProperties
{
    public function __construct(
        public string $a,
        public float $b,
        public ?DateTimeImmutable $c,
    ) {}
}
