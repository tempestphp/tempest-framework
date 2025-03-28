<?php
declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Inject;
use Tempest\Container\Lazy;

final class ClassWithLazySlowPropertyDependency
{
    #[Inject]
    #[Lazy]
    private(set) SlowDependency $dependency;

    public function __construct() {}
}
