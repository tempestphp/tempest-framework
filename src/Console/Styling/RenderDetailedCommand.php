<?php

declare(strict_types=1);

namespace Tempest\Console\Styling;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleConfig;
use Tempest\Console\ConsoleStyle;
use Tempest\Console\RenderConsoleCommand;

final readonly class RenderDetailedCommand
{
    public function __construct(
        protected ConsoleConfig $consoleConfig,
        protected RenderConsoleCommand $renderConsoleCommand,
    ) {

    }

    public function __invoke(ConsoleCommand $command): string
    {
        $arguments = $command->getAvailableArguments()->all();

        $formattedCommand = $this->renderConsoleCommand->__invoke($command, true);

        return ConsoleOutputBuilder::new()
            ->brand(" " . $command->getDescription() . " ")
            ->blank()
            ->comments($command->getHelpLines())
            ->formatted($formattedCommand)
            ->when(
                ! ! $command->getAliases(),
                fn (ConsoleOutputBuilder $b) => $b->blank()->muted('Aliases: ' . implode(', ', $command->getAliases())),
            )
            ->blank()
            ->when(! ! $arguments, function (ConsoleOutputBuilder $b) use ($arguments) {
                foreach ($arguments as $key => $argument) {
                    foreach ($argument->getHelpLines() as $line) {
                        $b->formatted(ConsoleStyle::FG_BLUE($key) . " - " . $line);
                    }
                }
            })
            ->toString();
    }
}
