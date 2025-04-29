<?php

declare(strict_types=1);

namespace Tempest\Mapper\Tests\Integration\Fixtures;

use Tempest\Mapper\MapFrom;

final readonly class ObjectWithMapFromAttribute
{
    public function __construct(
        #[MapFrom('name')]
        public string $fullName,
    ) {}
}
