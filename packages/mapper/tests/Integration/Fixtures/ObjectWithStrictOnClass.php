<?php

declare(strict_types=1);

namespace Tempest\Mapper\Tests\Integration\Fixtures;

use Tempest\Mapper\Strict;

#[Strict]
final readonly class ObjectWithStrictOnClass
{
    public function __construct(
        public string $a,
        public string $b,
    ) {}
}
