<?php

declare(strict_types=1);

namespace Tests\Tempest\Bus;

use App\Commands\MyCommand;
use function Tempest\command;
use Tempest\Commands\CommandBusConfig;
use Tempest\Commands\Middleware;
use Tempest\Interface\CommandBus;
use Tests\Tempest\TestCase;

class CommandBusTest extends TestCase
{
    /** @test */
    public function command_handlers_are_auto_discovered()
    {
        $command = new MyCommand();

        command($command);

        $bus = $this->container->get(CommandBus::class);

        $this->assertEquals([$command], $bus->getHistory());
    }

    /** @test */
    public function command_bus_with_middleware()
    {
        CommandBusMiddleware::$hit = false;

        $config = $this->container->get(CommandBusConfig::class);

        $config->addMiddleware(new CommandBusMiddleware());

        command(new MyCommand());

        $this->assertTrue(CommandBusMiddleware::$hit);
    }
}

class CommandBusMiddleware implements Middleware
{
    public static bool $hit = false;

    public function __invoke(object $command, callable $next): void
    {
        self::$hit = true;

        $next($command);
    }
}
