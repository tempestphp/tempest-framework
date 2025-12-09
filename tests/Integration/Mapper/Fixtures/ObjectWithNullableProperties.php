<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use DateTimeImmutable;
use Tempest\Mapper\SerializeAs;
use Tempest\Mapper\Strict;

#[Strict]
#[SerializeAs(self::class)]
final class ObjectWithNullableProperties
{
    public function __construct(
        public string $a,
        public float $b,
        public ?DateTimeImmutable $c,
    ) {}
}
