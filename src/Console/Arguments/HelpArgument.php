<?php

declare(strict_types=1);

namespace Tempest\Console\Arguments;

use Tempest\Console\ArgumentBag;
use Tempest\Console\ConsoleStyle;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleOutput;
use Tempest\Console\ExitException;
use Symfony\Component\Console\Color;
use Tempest\Console\InjectedArgument;
use Tempest\Console\RenderConsoleCommand;
use function Tempest\get;

final readonly class HelpArgument extends InjectedArgument
{
    public function handle(ConsoleCommand $command): void
    {
        $output = get(ConsoleOutput::class);

        $output->writeln(ConsoleStyle::BG_DARK_BLUE(" " . ConsoleStyle::FG_WHITE(ConsoleStyle::BOLD($command->getDescription() . " "))));

        $output->writeln((new RenderConsoleCommand())($command, true, false));
        $output->writeln("");

        if ($command->getAliases()) {
            $output->writeln(ConsoleStyle::FG_LIGHT_GRAY("Aliases: ") . implode(", ", $command->getAliases()));
        }

        foreach ($command->getAvailableArguments()->all() as $key => $argument) {
            $output->writeln(ConsoleStyle::FG_BLUE($key) . " - " . $argument->getHelp());
        }

        throw new ExitException();
    }

    public static function instance(): self
    {
        return new self(
            name: 'help',
            value: false,
            aliases: ['h'],
            description: 'Displays helpful information about the command.',
        );
    }
}
