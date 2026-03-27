<?php

declare(strict_types=1);

namespace Tempest\Console\Actions;

use BackedEnum;
use Tempest\Console\Console;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Console\Input\ConsoleArgumentDefinition;
use Tempest\Console\Input\ConsoleInputArgument;
use Tempest\Validation\Rules\IsBoolean;
use Tempest\Validation\Rules\IsEnum;
use Tempest\Validation\Rules\IsNotEmptyString;
use Tempest\Validation\Rules\IsNumeric;

/** @internal */
final readonly class PromptForConsoleInput
{
    public function __construct(
        private Console $console,
    ) {}

    public function __invoke(
        ConsoleArgumentBag $argumentBag,
        ConsoleArgumentDefinition $argumentDefinition,
    ): void {
        $isEnum = is_a($argumentDefinition->type, BackedEnum::class, allow_string: true);

        $value = match ($argumentDefinition->type) {
            'bool' => $this->console->confirm(
                question: $argumentDefinition->prompt ?? $argumentDefinition->name,
                default: $argumentDefinition->default ?? false,
            ),
            default => $this->console->ask(
                question: $argumentDefinition->prompt ?? $argumentDefinition->name,
                options: match (true) {
                    $isEnum => $argumentDefinition->type::cases(),
                    default => null,
                },
                default: $argumentDefinition->default,
                hint: $argumentDefinition->help ?: $argumentDefinition->description,
                validation: array_filter([
                    $isEnum
                        ? new IsEnum($argumentDefinition->type)
                        : new IsNotEmptyString(),
                    match ($argumentDefinition->type) {
                        'bool' => new IsBoolean(),
                        'int' => new IsNumeric(),
                        default => null,
                    },
                ]),
            ),
        };

        $argumentBag->add(new ConsoleInputArgument(
            name: $argumentDefinition->name,
            position: $argumentDefinition->position,
            value: $value,
        ));
    }
}
