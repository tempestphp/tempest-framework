<?php

namespace Tempest\Discovery\Rule;

use ReflectionClass;

final class AllOf implements MatchingRule
{
    private array $rules = [];

    public function __construct(MatchingRule ...$rules)
    {
        $this->rules = $rules;
    }

    public function match(string $class, ReflectionClass $reflectionClass): bool
    {
        return array_all(
            $this->rules,
            fn($rule) => $rule->match($class, $reflectionClass)
        );
    }
}