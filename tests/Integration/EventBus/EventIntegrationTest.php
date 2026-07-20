<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\EventBus;

use PHPUnit\Framework\Attributes\Test;
use Tests\Tempest\Fixtures\Events\ItHappened;
use Tests\Tempest\Fixtures\Events\ItHappenedHandler;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\EventBus\event;

/**
 * @internal
 */
final class EventIntegrationTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function event(): void
    {
        ItHappenedHandler::$itHappened = false;

        event(new ItHappened());

        $this->assertTrue(ItHappenedHandler::$itHappened);
    }
}
