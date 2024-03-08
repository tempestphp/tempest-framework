<?php

namespace Tempest\Discovery;

use ReflectionClass;

interface Discoverer
{
    public function discover(ReflectionClass $class): void;
}