<?php

declare(strict_types=1);

use Tempest\AppConfig;
use Tempest\Application\Application;
use Tempest\Application\ConsoleApplication;
use Tempest\Application\HttpApplication;
use Tempest\Console\ConsoleOutputInitializer;
use Tempest\Console\GenericConsoleOutput;
use Tempest\Console\NullConsoleOutput;
use Tests\Tempest\TestCase;

uses(TestCase::class);

test('in console application', function () {
    $initializer = new ConsoleOutputInitializer();

    $this->container->singleton(Application::class, fn () => new ConsoleApplication(
        [],
        $this->container,
        $this->container->get(AppConfig::class),
    ));

    $consoleOutput = $initializer->initialize($this->container);

    expect($consoleOutput)->toBeInstanceOf(GenericConsoleOutput::class);
});

test('in http application', function () {
    $initializer = new ConsoleOutputInitializer();

    $this->container->singleton(Application::class, fn () => new HttpApplication(
        $this->container,
        $this->container->get(AppConfig::class),
    ));

    $consoleOutput = $initializer->initialize($this->container);

    expect($consoleOutput)->toBeInstanceOf(NullConsoleOutput::class);
});
