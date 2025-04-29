<?php
declare(strict_types=1);

namespace Tempest\Container\Tests\Unit\Fixtures;

use Tempest\Container\Proxy;

final readonly class ClassWithLazySlowDependency
{
    public function __construct(
        #[Proxy]
        public SlowDependency $dependency,
    ) {}
}
