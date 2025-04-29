<?php

declare(strict_types=1);

namespace Tempest\EventBus\Tests\Integration;

use Tempest\Drift\FrameworkIntegrationTestCase;
use Tempest\EventBus\Tests\Unit\Fixtures\MyEventHandler;
use Tests\Tempest\Fixtures\Events\ItHappened;

use function Tempest\event;

/**
 * @internal
 */
final class EventIntegrationTestCase extends FrameworkIntegrationTestCase
{
    public function test_event(): void
    {
        MyEventHandler::$itHappened = false;

        event(new ItHappened());

        $this->assertTrue(MyEventHandler::$itHappened);
    }
}
