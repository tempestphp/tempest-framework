<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Container\Singleton;
use Tempest\Reflection\ClassReflector;
use UnitEnum;

final readonly class DynamicTaggedDependencyDynamicInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, UnitEnum|string|null $tag): bool
    {
        return $class->implements(DynamicTaggedDependencyWithInitializer::class);
    }

    #[Singleton(dynamicTags: true)]
    public function initialize(ClassReflector $class, UnitEnum|string|null $tag, Container $container): DynamicTaggedDependencyWithInitializer
    {
        return new DynamicTaggedDependencyWithInitializer($tag);
    }
}
