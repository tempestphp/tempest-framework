<?php

declare(strict_types=1);

namespace Tempest\Interfaces;

use ReflectionClass;

interface Discoverer
{
    public function discover(ReflectionClass $class): void;
}
