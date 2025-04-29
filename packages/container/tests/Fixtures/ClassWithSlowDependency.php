<?php
declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

final readonly class ClassWithSlowDependency
{
    public function __construct(
        public SlowDependency $dependency,
    ) {}
}
