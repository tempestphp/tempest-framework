<?php

namespace Tempest\Container\Discovery\ClassFactory;

use ReflectionClass;

final class ReflectionClassFactory implements ClassFactory
{
    public function create(string $class): object
    {
        $reflection = new ReflectionClass($class);

        // A bit hacky, but avoids instant problems.
        return $reflection->newInstanceWithoutConstructor();
    }
}