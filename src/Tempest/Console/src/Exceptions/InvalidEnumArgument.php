<?php

declare(strict_types=1);

namespace Tempest\Console\Exceptions;

use BackedEnum;
use Tempest\Console\Console;
use function array_map;
use function gettype;
use function implode;
use function is_string;

final class InvalidEnumArgument extends ConsoleException
{
    /**
     * @param class-string<BackedEnum> $argumentType
     */
    public function __construct(
        private string $argumentName,
        private string $argumentType,
        private mixed $value,
    ) {
    }

    public function render(Console $console): void
    {
        if (is_string($this->value) || is_numeric($this->value)) {
            $value = "`{$this->value}`";
        } else {
            $value = 'of type `' . gettype($this->value) . '`';
        }

        $cases = array_map(
            callback: fn (BackedEnum $case) => $case->value,
            array: $this->argumentType::cases(),
        );
        $console->error("Invalid argument {$value} for `{$this->argumentName}` argument, valid values are: " . implode(', ', $cases));
    }
}
