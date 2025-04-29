<?php

declare(strict_types=1);

namespace Tempest\EventBus\Tests\Unit\Fixtures;

use Tempest\EventBus\EventHandler;

final class EventInterfaceHandler
{
    public static bool $itHappened = false;

    #[EventHandler]
    public function handleItHappened(EventInterface $event): void
    {
        self::$itHappened = true;
    }
}
