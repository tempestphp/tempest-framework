<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Mapper\Attributes\MapTo;

final class ObjectWithMapToAttribute
{
    public function __construct(
        #[MapTo('name')]
        public readonly string $fullName,
    ) {
    }
}
