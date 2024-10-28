<?php

declare(strict_types=1);

namespace Tempest\Console;

use Attribute;
use Closure;
use Tempest\Generation\StubFileGenerator;
use function Tempest\get;
use function Tempest\Support\arr;

/**
 * Defines a console command that is specifically for generating files.
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class GeneratorCommand extends ConsoleCommand
{
    /**
     * Make the handler for the given command.
     * This allow the console to run this handler without altering the command structure.
     *
     * @return Closure(array<mixed> $params) The command handler.
     */
    public function makeHandler(): Closure
    {
        return function (array $params): void {
            // Resolve all generators and run them.
            arr(
                $this->handler->invokeArgs(
                    get($this->handler->getDeclaringClass()->getName()),
                    $params
                )
            )
                ->filter(fn ($generator) => $generator instanceof StubFileGenerator)
                ->each(fn (StubFileGenerator $generator) => $generator->generate());
        };
    }
}
