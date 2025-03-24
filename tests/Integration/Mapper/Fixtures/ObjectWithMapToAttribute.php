<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Mapper\MapTo;

final readonly class ObjectWithMapToAttribute
{
    public function __construct(
        #[MapTo('name')]
        public string $fullName,
    ) {}
}
