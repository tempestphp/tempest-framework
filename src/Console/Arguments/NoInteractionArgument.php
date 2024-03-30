<?php

declare(strict_types=1);

namespace Tempest\Console\Arguments;

use Tempest\Console\ConsoleInput;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\InjectedArgument;
use Tempest\Console\NullConsoleInput;
use function Tempest\get;
use function Tempest\swap;

final readonly class NoInteractionArgument extends InjectedArgument
{
    public function handle(ConsoleCommand $command): void
    {
        swap(ConsoleInput::class, fn () => get(NullConsoleInput::class));
    }

    public static function instance(): self
    {
        return new self(
            name: 'no-interaction',
            value: false,
            default: false,
            aliases: ['n'],
            description: 'Disable console input',
            parameter: self::bool(),
        );
    }

    public function shouldInject(): bool
    {
        return !! $this->value;
    }
}
