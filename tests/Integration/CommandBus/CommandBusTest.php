<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\CommandBus;

use App\Commands\MyCommand;
use function Tempest\command;
use Tempest\Commands\CommandBus;
use Tempest\Commands\CommandBusConfig;
use Tempest\Commands\CommandHandlerNotFound;
use Tests\Tempest\Integration\CommandBus\Fixtures\MyCommandBusMiddleware;
use Tests\Tempest\Integration\FrameworkIntegrationTest;

/**
 * @internal
 * @small
 */
class CommandBusTest extends FrameworkIntegrationTest
{
    public function test_command_handlers_are_auto_discovered()
    {
        $command = new MyCommand();

        command($command);

        $bus = $this->container->get(CommandBus::class);

        $this->assertEquals([$command], $bus->getHistory());
    }

    public function test_command_bus_with_middleware()
    {
        MyCommandBusMiddleware::$hit = false;

        $config = $this->container->get(CommandBusConfig::class);

        $config->addMiddleware(new MyCommandBusMiddleware());

        command(new MyCommand());

        $this->assertTrue(MyCommandBusMiddleware::$hit);
    }

    public function test_unknown_handler_throws_exception()
    {
        $this->expectException(CommandHandlerNotFound::class);

        command(new class () {});
    }
}
