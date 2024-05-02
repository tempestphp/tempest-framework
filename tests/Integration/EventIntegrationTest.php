<?php

namespace Tests\Tempest\Integration;

use App\Events\ItHappened;
use App\Events\MyEventHandler;
use Tests\Tempest\IntegrationTest;
use function Tempest\event;

class EventIntegrationTest extends IntegrationTest
{
    public function test_event(): void
    {
        MyEventHandler::$itHappened = false;

        event(new ItHappened());

        $this->assertTrue(MyEventHandler::$itHappened);
    }
}