<?php

namespace Tempest\Interfaces;

use ReflectionClass;

interface Discoverer
{
    public function discover(ReflectionClass $class): void;
}