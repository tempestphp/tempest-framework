<?php

namespace Tests\Tempest\Integration\Core\Fixtures;

use Tempest\Container\Tag;
use Tests\Tempest\Fixtures\TaggedConfigExample;

final class HasTaggedConfig
{
    public function __construct(
        #[Tag('tagged1')]
        public readonly TaggedConfigExample $config1,
        #[Tag('tagged2')]
        public readonly TaggedConfigExample $config2,
    ) {}
}
