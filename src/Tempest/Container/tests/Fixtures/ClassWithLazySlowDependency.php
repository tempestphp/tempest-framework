<?php
declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Lazy;

final readonly class ClassWithLazySlowDependency
{
    public function __construct(
        #[Lazy]
        public SlowDependency $dependency,
    ) {}
}
