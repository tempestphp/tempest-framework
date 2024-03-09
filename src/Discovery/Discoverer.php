<?php

declare(strict_types=1);

namespace Tempest\Discovery;

use ReflectionClass;

interface Discoverer
{
    public function discover(ReflectionClass $class): void;
}
