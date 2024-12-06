<?php

namespace Tempest\Container\Discovery\Agent;

use ReflectionClass;

interface DiscoveryAgent
{
    public function inspect(ReflectionClass $class): void;
}