<?php

declare(strict_types=1);

namespace Tempest\EventBus\Tests\Integration;

use Tempest\EventBus\Tests\Unit\Fixtures\MyEventHandler;
use Tests\Tempest\Fixtures\Events\ItHappened;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

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
