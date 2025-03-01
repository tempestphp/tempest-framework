<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Mapper\Attributes\MapTo;

final class ObjectWithMappedVariousPropertyScope
{
    public function __construct(
        #[MapTo('private')] // @phpstan-ignore-line property.onlyWritten
        private string $privateProp,
        #[MapTo('protected')]
        protected string $protectedProp,
        #[MapTo('public')]
        public string $publicProp,
    ) {
    }
}
