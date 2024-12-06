<?php

namespace Tempest\Container\Discovery\ClassLoader;

use ReflectionClass;

interface ClassLoader
{
    /**
     * @return array<ReflectionClass>
     */
    public function load(): array;
}