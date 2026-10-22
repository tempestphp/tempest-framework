<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

final readonly class ResolvedTwiceParent
{
    public function __construct(
        public ResolvedTwiceLeaf $first,
        public ResolvedTwiceLeaf $second,
    ) {}
}
