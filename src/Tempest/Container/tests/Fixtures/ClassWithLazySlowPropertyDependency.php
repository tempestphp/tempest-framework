<?php
declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Inject;
use Tempest\Container\Lazy;

final readonly class ClassWithLazySlowPropertyDependency
{
    #[Inject]
    #[Lazy]
    public SlowDependency $dependency;

    public function __construct() {}
}
