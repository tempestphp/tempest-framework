<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Handlers;

use Tempest\EventBus\EventHandler;
use Tests\Tempest\Fixtures\Events\EventInterface;

final class EventInterfaceHandler
{
    public static bool $itHappened = false;

    #[EventHandler]
    public function handleItHappened(EventInterface $event): void
    {
        self::$itHappened = true;
    }
}
