<?php

namespace Tempest\Discovery\Rule;

use ReflectionClass;

interface MatchingRule
{
    public function match(string $class, ReflectionClass $reflectionClass): bool;
}