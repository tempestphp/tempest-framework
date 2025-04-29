<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Unit\Fixtures;

use Tempest\Container\Tag;

final readonly class DependencyWithTaggedDependency
{
    public function __construct(
        #[Tag('web')]
        public TaggedDependency $dependency,
    ) {}
}
