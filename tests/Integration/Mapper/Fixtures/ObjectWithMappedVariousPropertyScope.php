<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Mapper\MapTo;

final class ObjectWithMappedVariousPropertyScope
{
    public function __construct(
        #[MapTo('public')]
        public string $publicProp,
    ) {
    }
}
