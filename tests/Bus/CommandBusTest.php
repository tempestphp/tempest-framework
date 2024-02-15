<?php

namespace Tests\Tempest\Bus;

use Tempest\Http\Dispatch;
use Tests\Tempest\TestCase;
use function Tempest\command;

class CommandBusTest extends TestCase
{
    /** @test */
    public function command_handlers_are_auto_discovered()
    {
        command(new Dispatch());
    }
}
