<?php
declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Inject;
use Tempest\Container\Sometimes;

final readonly class ClassWithLazySlowPropertyDependency
{
    #[Inject]
    #[Sometimes]
    public SlowDependency $dependency;

    public function __construct() {}
}
