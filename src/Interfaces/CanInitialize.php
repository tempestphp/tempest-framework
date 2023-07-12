<?php

namespace Tempest\Interfaces;

interface CanInitialize extends Initializer
{
    public function canInitialize(string $className): bool;
}
