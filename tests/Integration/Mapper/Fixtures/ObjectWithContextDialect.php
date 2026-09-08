<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Mapper\CastWith;

final readonly class ObjectWithContextDialect
{
    public function __construct(
        #[CastWith(ContextDialectCaster::class)]
        public string $dialect,
    ) {}
}
