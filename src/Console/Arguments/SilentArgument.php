<?php

declare(strict_types=1);

namespace Tempest\Console\Arguments;

use Tempest\Console\ConsoleOutput;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\InjectedArgument;
use Tempest\Console\NullConsoleOutput;
use function Tempest\get;
use function Tempest\swap;

final readonly class SilentArgument extends InjectedArgument
{
    public function handle(ConsoleCommand $command): void
    {
        swap(ConsoleOutput::class, fn () => get(NullConsoleOutput::class));
    }

    public static function instance(): self
    {
        return new self(
            name: 'silent',
            value: false,
            aliases: ['s'],
            description: 'Disable console output',
        );
    }

    public function shouldInject(): bool
    {
        return !! $this->value;
    }
}
