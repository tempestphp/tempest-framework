<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\CommandBus;

use function Tempest\command;
use Tempest\CommandBus\CommandBus;
use Tempest\CommandBus\CommandBusConfig;
use Tempest\CommandBus\CommandHandlerNotFound;
use Tests\Tempest\Fixtures\Commands\MyCommand;
use Tests\Tempest\Integration\CommandBus\Fixtures\MyCommandBusMiddleware;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class CommandBusTest extends FrameworkIntegrationTestCase
{
    public function test_command_handlers_are_auto_discovered(): void
    {
        $command = new MyCommand();

        command($command);

        $bus = $this->container->get(CommandBus::class);

        $this->assertEquals([$command], $bus->getHistory());
    }

    public function test_command_bus_with_middleware(): void
    {
        MyCommandBusMiddleware::$hit = false;

        $config = $this->container->get(CommandBusConfig::class);

        $config->addMiddleware(new MyCommandBusMiddleware());

        command(new MyCommand());

        $this->assertTrue(MyCommandBusMiddleware::$hit);
    }

    public function test_unknown_handler_throws_exception(): void
    {
        $this->expectException(CommandHandlerNotFound::class);

        command(new class () {});
    }
}
