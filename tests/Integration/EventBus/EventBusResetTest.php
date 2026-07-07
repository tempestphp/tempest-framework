<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\EventBus;

use PHPUnit\Framework\Attributes\Test;
use Tempest\EventBus\EventBus;
use Tempest\EventBus\EventBusConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class EventBusResetTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function reset_clears_runtime_closure_handlers(): void
    {
        /** @var EventBusConfig $eventBusConfig */
        $eventBusConfig = $this->container->get(EventBusConfig::class);

        /** @var EventBus $eventBus */
        $eventBus = $this->container->get(EventBus::class);

        $hits = 0;

        $eventBus->listen(
            handler: function (string $event) use (&$hits): void {
                $this->assertSame('runtime-event', $event);

                $hits++;
            },
            event: 'runtime-event',
        );

        $this->assertArrayHasKey('runtime-event', $eventBusConfig->closureHandlers);

        $eventBus->dispatch('runtime-event');

        $this->assertSame(1, $hits);

        $this->container->reset();

        $this->assertSame([], $eventBusConfig->closureHandlers);

        /** @var EventBus $eventBus */
        $eventBus = $this->container->get(EventBus::class);

        $hits = 0;

        $eventBus->dispatch('runtime-event');

        $this->assertSame(0, $hits);
    }
}
