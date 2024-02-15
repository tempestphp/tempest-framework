<?php

declare(strict_types=1);

namespace Tests\Tempest\Bus;

use function Tempest\command;
use Tempest\Http\Dispatch;
use Tests\Tempest\TestCase;

class CommandBusTest extends TestCase
{
    /** @test */
    public function command_handlers_are_auto_discovered()
    {
        command(new Dispatch());
    }
}
