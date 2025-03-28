<?php
declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

final readonly class ClassWithSlowDependency
{

    final public function __construct(public SlowDependency $dependency) {

    }
}
