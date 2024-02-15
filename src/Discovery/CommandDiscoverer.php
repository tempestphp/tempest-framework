<?php

namespace Tempest\Discovery;

use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use Tempest\Interface\Command;
use Tempest\Interface\CommandBus;
use Tempest\Interface\Discoverer;

final readonly class CommandDiscoverer implements Discoverer
{
    public function __construct(private CommandBus $commandBus) {}

    public function discover(ReflectionClass $class): void
    {
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isConstructor() || $method->isDestructor()) {
                continue;
            }

            $parameters = $method->getParameters();

            if (count($parameters) !== 1) {
                continue;
            }

            $firstParameter = $parameters[0];

            $type = $firstParameter->getType();

            if (! $type instanceof ReflectionNamedType) {
                continue;
            }

            if (! class_exists($type->getName())) {
                continue;
            }

            $typeClass = new ReflectionClass($type->getName());

            if (! $typeClass->implementsInterface(Command::class)) {
                continue;
            }

            $this->commandBus->addHandler($typeClass->getName(), $method);
        }
    }
}