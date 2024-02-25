<?php

declare(strict_types=1);

use App\Commands\MyCommand;
use Tempest\Commands\CommandBus;
use Tempest\Commands\CommandBusConfig;
use Tempest\Commands\CommandHandlerNotFound;
use Tests\Tempest\CommandBus\MyCommandBusMiddleware;
use Tests\Tempest\TestCase;
use function Tempest\command;

uses(TestCase::class);

test('command handlers are auto discovered', function () {
	$command = new MyCommand();

	command($command);

	$bus = $this->container->get(CommandBus::class);

	expect($bus->getHistory())->toEqual([$command]);
});

test('command bus with middleware', function () {
	MyCommandBusMiddleware::$hit = false;

	$config = $this->container->get(CommandBusConfig::class);

	$config->addMiddleware(new MyCommandBusMiddleware());

	command(new MyCommand());

	expect(MyCommandBusMiddleware::$hit)->toBeTrue();
});

test('unknown handler throws exception', function () {
	$this->expectException(CommandHandlerNotFound::class);

	command(new class () {
	});
});
