<?php

declare(strict_types=1);

namespace Tempest\Reflection\Tests\Unit\Fixtures;

final class A
{
    public function __construct(
        public B $b,
    ) {}
}
