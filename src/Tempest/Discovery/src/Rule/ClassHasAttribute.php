<?php

namespace Tempest\Discovery\Rule;

use ReflectionClass;

final class ClassHasAttribute implements MatchingRule
{
    public function __construct(private readonly string $attribute)
    {}

    public function match(string $class, ReflectionClass $reflectionClass): bool
    {
        $attribute = $reflectionClass->getAttributes($this->attribute);

        return count($attribute) > 0;
    }
}