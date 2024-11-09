<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Console;

use Tempest\Console\ConsoleArgument;

// tests/Integration/Console/Input/ConsoleArgumentDefinitionTest.php
final readonly class CommandWithDifferentArguments
{
    public function __invoke(
        string $string,
        string $camelCaseString,
        #[ConsoleArgument(name: 'my-kebab-string')]
        string $renamedKebabString,
        #[ConsoleArgument(name: 'myCamelString')]
        string $renamedCamelString,
        bool $bool,
        bool $camelCaseBool,
        string $camelCaseStringWithDefault = 'foo',
        bool $camelCaseBoolWithTrueDefault = true,
        bool $camelCaseBoolWithFalseDefault = false,
    ): void {
    }
}
