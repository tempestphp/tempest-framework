<?php
declare(strict_types=1);

namespace Tempest\Container\Tests\Unit\Fixtures;

final readonly class ClassWithSlowDependency
{
    public function __construct(
        public SlowDependency $dependency,
    ) {}
}
