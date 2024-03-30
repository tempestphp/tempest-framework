<?php

declare(strict_types=1);

namespace Tempest\Console\Arguments;

use Tempest\AppConfig;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleInput;
use Tempest\Console\ExitException;
use Tempest\Console\InjectedArgument;
use function Tempest\get;

final readonly class ForceArgument extends InjectedArgument
{
    public function handle(ConsoleCommand $command): void
    {
        $input = get(ConsoleInput::class);
        $config = get(AppConfig::class);

        if (
            $config->environment->isProduction() &&
            ! $input->confirm("This operation could be dangerous. Are you sure you want to continue?")
        ) {
            throw new ExitException();
        }
    }

    public static function instance(): self
    {
        return new self(
            name: 'force',
            value: false,
            default: false,
            aliases: ['f'],
            description: 'Force the operation to run without asking for confirmation.',
            parameter: self::bool(),
        );
    }

    public function shouldInject(): bool
    {
        return ! $this->value;
    }
}
