<?php

declare(strict_types=1);

use function Tempest\attribute;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleStyle;
use Tempest\Console\RenderConsoleCommand;
use Tests\Tempest\Console\Fixtures\MyConsole;
use Tests\Tempest\TestCase;

uses(TestCase::class);

test('render', function () {
    $handler = new ReflectionMethod(new MyConsole(), 'handle');

    $consoleCommand = attribute(ConsoleCommand::class)->in($handler)->first();

    $consoleCommand->setHandler($handler);

    $string = str_replace(
        [
            ConsoleStyle::FG_BLUE->value,
            ConsoleStyle::FG_DARK_BLUE->value,
            ConsoleStyle::RESET->value,
            ConsoleStyle::ESC->value,
        ],
        '',
        (new RenderConsoleCommand())($consoleCommand)
    );

    expect($string)->toBe('test <path> [times=1] [--force=false] - description');
});
