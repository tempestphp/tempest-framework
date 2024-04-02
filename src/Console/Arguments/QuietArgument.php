<?php

declare(strict_types=1);

namespace Tempest\Console\Arguments;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleOutput;
use Tempest\Console\InjectedArgument;
use Tempest\Console\NullConsoleOutput;
use function Tempest\get;
use function Tempest\swap;

final readonly class QuietArgument extends InjectedArgument
{
    public function handle(ConsoleCommand $command): void
    {
        swap(ConsoleOutput::class, fn () => get(NullConsoleOutput::class));
    }

    public static function instance(): self
    {
        return new self(
            name: 'quiet',
            value: false,
            default: false,
            aliases: ['q'],
            description: 'Disable console output',
            parameter: self::bool(),
        );
    }

    public function shouldInject(): bool
    {
        return ! ! $this->value;
    }
}
