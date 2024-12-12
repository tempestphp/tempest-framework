<?php

namespace Tempest\Container\Discovery\Agent;

use ReflectionClass;
use Tempest\Console\ConsoleCommand;
use Tempest\Container\Container;
use function Tempest\reflect;

final class SampleAgent implements DiscoveryAgent
{
    public function inspect(ReflectionClass $class): void
    {
        foreach (reflect($class)->getPublicMethods() as $method) {
            if ($method->hasAttribute(ConsoleCommand::class)) {
//                var_dump($class->getName() . '::' . $method->getName());
            }
        }
    }
}