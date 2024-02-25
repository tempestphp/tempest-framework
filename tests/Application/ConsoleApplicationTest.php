<?php

declare(strict_types=1);

use Tempest\AppConfig;
use Tempest\Application\CommandNotFound;
use Tempest\Application\ConsoleApplication;
use Tempest\Console\ConsoleOutput;
use Tests\Tempest\TestCase;

uses(TestCase::class);

test('run', function () {
    $app = new ConsoleApplication(
        ['hello:world input'],
        $this->container,
        $this->container->get(AppConfig::class),
    );

    $app->run();

    /** @var \Tests\Tempest\TestConsoleOutput $output */
    $output = $this->container->get(ConsoleOutput::class);

    $this->assertStringContainsString('Tempest Console', $output->lines[0]);
});

test('unhandled command', function () {
    $this->expectException(CommandNotFound::class);

    $this->console('unknown');
});

test('cli application', function () {
    $output = $this->console('hello:world input');

    expect($output->lines)->toBe(['Hi', 'input']);
});

test('cli application flags', function () {
    $output = $this->console('hello:test --flag --optionalValue=1');

    expect($output->lines)->toBe(['1', 'flag']);
});

test('cli application flags defaults', function () {
    $output = $this->console('hello:test');

    expect($output->lines)->toBe(['null', 'no-flag']);
});

test('failing command', function () {
    $output = $this->console('hello:world');

    expect($output->lines)->toBe(['Something went wrong']);
});
