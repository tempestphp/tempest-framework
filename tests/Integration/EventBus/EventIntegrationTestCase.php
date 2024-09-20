<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\EventBus;

use function Tempest\event;
use Tempest\EventBus\Tests\Fixtures\MyEventHandler;
use Tests\Tempest\Fixtures\Events\ItHappened;
use Tests\Tempest\Integration\IntegrationTestCase;

/**
 * @internal
 */
final class EventIntegrationTestCase extends IntegrationTestCase
{
    public function test_event(): void
    {
        MyEventHandler::$itHappened = false;

        event(new ItHappened());

        $this->assertTrue(MyEventHandler::$itHappened);
    }
}
