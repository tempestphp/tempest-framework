<?php

declare(strict_types=1);

namespace Tests\Tempest\Bus;

use App\Commands\MyCommand;
use function Tempest\command;
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
}
