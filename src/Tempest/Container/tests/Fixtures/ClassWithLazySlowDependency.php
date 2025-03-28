<?php
declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Sometimes;

final readonly class ClassWithLazySlowDependency
{

    final public function __construct(#[Sometimes] public SlowDependency $dependency) {

    }
}
