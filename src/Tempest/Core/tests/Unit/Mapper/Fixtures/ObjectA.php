<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Mapper\Fixtures;

final class ObjectA
{
    public function __construct(
        public string $a,
        public string $b,
    ) {
    }
}
