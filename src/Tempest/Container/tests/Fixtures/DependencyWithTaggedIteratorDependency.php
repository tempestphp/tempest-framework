<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Tagged;

final readonly class DependencyWithTaggedIteratorDependency
{
    public function __construct(
        #[Tagged("tag")] public iterable $param
    ) {}
}