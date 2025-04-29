<?php
declare(strict_types=1);

namespace Tempest\Container\Tests\Unit\Fixtures;

use Tempest\Container\Inject;
use Tempest\Container\Proxy;

final class ClassWithLazySlowPropertyDependency
{
    #[Inject]
    #[Proxy]
    private(set) SlowDependency $dependency;

    public function __construct() {}
}
