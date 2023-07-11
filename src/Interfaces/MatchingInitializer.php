<?php

namespace Tempest\Interfaces;

interface MatchingInitializer extends Initializer
{
    public function canInitialize(string $className): bool;
}
