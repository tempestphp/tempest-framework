<?php

declare(strict_types=1);

namespace Tempest\EventBus\Tests;

use function Tempest\event;
use Tests\Tempest\Fixtures\Events\ItHappened;
use Tests\Tempest\Fixtures\Events\MyEventHandler;
use Tests\Tempest\Unit\IntegrationTestCase;

/**
 * @internal
 * @small
 */
class EventIntegrationTestCase extends IntegrationTestCase
{
    public function test_event(): void
    {
        MyEventHandler::$itHappened = false;

        event(new ItHappened());

        $this->assertTrue(MyEventHandler::$itHappened);
    }
}
