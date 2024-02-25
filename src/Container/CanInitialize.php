<?php

declare(strict_types=1);

namespace Tempest\Container;

interface CanInitialize
{
    public function canInitialize(string $className): bool;
}
