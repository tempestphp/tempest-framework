<?php

declare(strict_types=1);

namespace Tempest\Reflection\Tests\Unit\Fixtures;

final class B
{
    public function __construct(
        public C $c,
    ) {}
}
