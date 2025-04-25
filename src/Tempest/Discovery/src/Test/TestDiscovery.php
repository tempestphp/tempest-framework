<?php

namespace Tempest\Discovery\Test;

use ReflectionClass;
use Tempest\Console\ConsoleCommand;
use Tempest\Discovery\Rule\ClassHasAttribute;
use Tempest\Discovery\Rule\MatchingRule;

final class TestDiscovery implements NewDiscoveryAgent
{
    public function rules(): MatchingRule
    {
        return new ClassHasAttribute(
            attribute: ConsoleCommand::class
        );
    }

    public function run(string $class, ReflectionClass $reflectionClass)
    {
        // TODO: Implement run() method.
    }
}

new DiscoveryManager()
    ->addAgent(new TestDiscovery())
    ->addLocation(__DIR__)
    ->run();