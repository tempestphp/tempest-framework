<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration;

use App\Events\ItHappened;
use App\Events\MyEventHandler;
use function Tempest\event;
use Tests\Tempest\IntegrationTest;

/**
 * @internal
 * @small
 */
class EventIntegrationTest extends IntegrationTest
{
    public function test_event(): void
    {
        MyEventHandler::$itHappened = false;

        event(new ItHappened());

        $this->assertTrue(MyEventHandler::$itHappened);
    }
}
