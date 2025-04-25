<?php

namespace Tempest\Discovery\Test;

use ReflectionClass;
use Tempest\Discovery\Rule\MatchingRule;

interface NewDiscoveryAgent
{
    public function rules(): MatchingRule;

    public function run(string $class, ReflectionClass $reflectionClass);
}