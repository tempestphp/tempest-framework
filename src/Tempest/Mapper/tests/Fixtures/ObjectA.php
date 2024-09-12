<?php

declare(strict_types=1);

namespace Tempest\Mapper\Tests\Fixtures;

use Tempest\Mapper\Strict;

#[Strict]
final class ObjectA
{
    public function __construct(
        public string $a,
        public string $b,
    ) {
    }
}
