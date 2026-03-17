<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\EventBus;

use Tests\Tempest\Fixtures\Events\ItHappened;
use Tests\Tempest\Fixtures\Events\ItHappenedHandler;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\EventBus\event;

/**
 * @internal
 */
final class EventIntegrationTest extends FrameworkIntegrationTestCase
{
    public function test_event(): void
    {
        ItHappenedHandler::$itHappened = false;

        event(new ItHappened());

        $this->assertTrue(ItHappenedHandler::$itHappened);
    }
}
