<?php

declare(strict_types=1);

namespace Tempest\Console\Arguments;

use Tempest\Console\ExitException;
use Tempest\Console\ConsoleOutput;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\InjectedArgument;
use Tempest\Console\RenderConsoleCommand;
use function Tempest\get;

final readonly class HelpArgument extends InjectedArgument
{
    public function handle(ConsoleCommand $command): void
    {
        $output = get(ConsoleOutput::class);

        $output->writeln((new RenderConsoleCommand())($command));
        $output->writeln("");

        foreach ($command->getAvailableArguments()->all() as $key => $argument) {
            $output->writeln("$key => " . $argument->getHelp());
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
