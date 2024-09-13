<?php

declare(strict_types=1);

<<<<<<<< HEAD:tests/Integration/EventBus/EventIntegrationTestCase.php
namespace Tests\Tempest\Integration\EventBus;
========
namespace Tempest\EventBus\Tests;
>>>>>>>> main:src/Tempest/EventBus/tests/EventIntegrationTestCase.php

use function Tempest\event;
use Tests\Tempest\Fixtures\Events\ItHappened;
use Tests\Tempest\Fixtures\Events\MyEventHandler;
use Tests\Tempest\Integration\IntegrationTestCase;

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
