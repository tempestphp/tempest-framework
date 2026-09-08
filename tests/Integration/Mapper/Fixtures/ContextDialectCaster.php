<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Database\DatabaseContext;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Mapper\Caster;

#[SkipDiscovery]
final readonly class ContextDialectCaster implements Caster
{
    public function __construct(
        private DatabaseContext $context,
    ) {}

    public function cast(mixed $input): string
    {
        return $this->context->dialect->name;
    }
}
