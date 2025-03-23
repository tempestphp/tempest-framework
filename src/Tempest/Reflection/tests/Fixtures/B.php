<?php

declare(strict_types=1);

namespace Tempest\Reflection\Tests\Fixtures;

final class B
{
    public function __construct(
        public C $c,
    ) {}
}
