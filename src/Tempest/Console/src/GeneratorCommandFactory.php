<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Generation\StubFileGenerator;
use Tempest\Container\Container;

use function Tempest\Support\arr;

/**
 * The factory responsible to create a valid handler for a GeneratorCommand without altering the command structure.
 */
final class GeneratorCommandFactory
{
    public function __construct(
        protected readonly Container $container,
    ) {}

    /**
     * Make the handler for the given command.
     *
     * @param GeneratorCommand $command The GeneratorCommand definition.
     *
     * @return \Closure The command handler.
     */
    public function makeHandler(GeneratorCommand $command): \Closure {
        return function (array $params) use ($command) {
            // Resolve all generators and run them.
            arr(
                $command->handler->invokeArgs(
                    $this->container->get($command->handler->getDeclaringClass()->getName()),
                    $params
                )
            )
                ->filter(fn($generator) => $generator instanceof StubFileGenerator)
                ->each(fn(StubFileGenerator $generator) => $generator->generate());
        };
    }
}
