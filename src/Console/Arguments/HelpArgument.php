<?php

declare(strict_types=1);

namespace Tempest\Console\Arguments;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleOutput;
use Tempest\Console\ExitException;
use Tempest\Console\InjectedArgument;
use Tempest\Console\Styling\RenderDetailedCommand;
use function Tempest\get;

final readonly class HelpArgument extends InjectedArgument
{
    public function handle(ConsoleCommand $command): void
    {
        $output = get(ConsoleOutput::class);

        $output->writeln(
            get(RenderDetailedCommand::class)($command)
        );

        throw new ExitException();
    }

    public static function instance(): self
    {
        return new self(
            name: 'help',
            value: false,
            default: false,
            aliases: ['h'],
            description: 'Displays helpful information about the command.',
            parameter: self::bool(),
        );
    }
}
