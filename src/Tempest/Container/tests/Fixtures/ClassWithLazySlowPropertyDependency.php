<?php
declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Proxy;
use Tempest\Container\Inject;

final class ClassWithLazySlowPropertyDependency
{
    #[Inject]
    #[Proxy]
    private(set) SlowDependency $dependency;

    public function __construct() {}
}
