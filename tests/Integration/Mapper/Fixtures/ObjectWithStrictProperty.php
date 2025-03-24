<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Mapper\Strict;

final readonly class ObjectWithStrictProperty
{
    public function __construct(
        #[Strict]
        public string $a,
        public string $b,
    ) {}
}
