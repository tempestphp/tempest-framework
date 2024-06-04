<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container\Fixtures;

use Tempest\Container\Tag;

final readonly class DependencyWithTaggedDependency
{
    public function __construct(
        #[Tag('web')]
        public TaggedDependency $dependency,
    ) {
    }
}
