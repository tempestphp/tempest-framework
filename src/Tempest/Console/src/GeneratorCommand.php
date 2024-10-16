<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Reflection\MethodReflector;

use function Tempest\get;

/**
 * Defines a console command that is specifically for generating files.
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class GeneratorCommand extends ConsoleCommand {
    /**
     * The command handler method that's binded to the attribute.
     * Used to be passed to the handlerCallback method.
     */
    protected MethodReflector $commandHandler;
    
    public function setHandler(MethodReflector $handler): self
    {
        $this->commandHandler = $handler;
        $this->handler        = MethodReflector::fromParts($this, 'handlerCallback');

        return $this;
    }

    /**
     * The handler callback that will proxy the command to run multiple handlers itself.
     *
     * @param array<mixed> $params The parameters passed to the command.
     */
    public function handlerCallback(array $params): void
    {
        $commandInstance   = get($this->commandHandler->getDeclaringClass()->getName());
        $stubFileGenerator = $this->commandHandler->invokeArgs(
            $commandInstance,
            $params
        );

        $stubFileGenerator->generate();
    }
}
