<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\CommandBus;

use PHPUnit\Framework\Attributes\Test;
use Tempest\CommandBus\CommandBus;
use Tempest\CommandBus\CommandBusConfig;
use Tempest\CommandBus\CommandHandlerWasNotFound;
use Tests\Tempest\Fixtures\Commands\MyBrokenCommand;
use Tests\Tempest\Fixtures\Commands\MyCommand;
use Tests\Tempest\Fixtures\Commands\MyCommandBusMiddleware;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\CommandBus\command;

/**
 * @internal
 */
final class CommandBusTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function command_handlers_are_auto_discovered(): void
    {
        $command = new MyCommand();

        command($command);

        $bus = $this->container->get(CommandBus::class);

        $this->assertEquals([$command], $bus->getHistory());
    }

    #[Test]
    public function command_bus_with_middleware(): void
    {
        MyCommandBusMiddleware::$hit = false;

        command(new MyCommand());

        $this->assertTrue(MyCommandBusMiddleware::$hit);
    }

    #[Test]
    public function unknown_handler_throws_exception(): void
    {
        $this->expectException(CommandHandlerWasNotFound::class);

        command(new class() {});
    }

    #[Test]
    public function command_handlers_with_more_than_one_argument_arent_discovered(): void
    {
        $commandBusConfig = $this->container->get(CommandBusConfig::class);

        $this->assertNull($commandBusConfig->handlers[MyBrokenCommand::class] ?? null);
    }

    #[Test]
    public function command_handlers_with_no_proper_object_as_their_argument_are_not_discovered(): void
    {
        $commandBusConfig = $this->container->get(CommandBusConfig::class);

        $this->assertNull($commandBusConfig->handlers['string'] ?? null);
    }
}
