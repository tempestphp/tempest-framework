<?php

declare(strict_types=1);

namespace Tempest\Interfaces;

interface CanInitialize extends Initializer
{
    public function canInitialize(string $className): bool;
}
