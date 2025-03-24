<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Console;

use Tempest\Console\ConsoleArgument;

// tests/Integration/Console/Input/ConsoleArgumentDefinitionTest.php
final readonly class CommandWithDifferentArguments
{
    public function __invoke(
        string $string, // @mago-expect best-practices/no-unused-parameter
        string $camelCaseString, // @mago-expect best-practices/no-unused-parameter
        #[ConsoleArgument(name: 'my-kebab-string')]
        string $renamedKebabString, // @mago-expect best-practices/no-unused-parameter
        #[ConsoleArgument(name: 'myCamelString')]
        string $renamedCamelString, // @mago-expect best-practices/no-unused-parameter
        bool $bool, // @mago-expect best-practices/no-unused-parameter
        bool $camelCaseBool, // @mago-expect best-practices/no-unused-parameter
        string $camelCaseStringWithDefault = 'foo', // @mago-expect best-practices/no-unused-parameter
        bool $camelCaseBoolWithTrueDefault = true, // @mago-expect best-practices/no-unused-parameter
        bool $camelCaseBoolWithFalseDefault = false, // @mago-expect best-practices/no-unused-parameter
    ): void {
    }
}
