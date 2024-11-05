<?php

declare(strict_types=1);

namespace Tempest\EventBus\Tests\Fixtures;

use Tempest\EventBus\EventHandler;

final class MyInterfaceEventHandler
{
    public static bool $itHappened = false;

    #[EventHandler]
    public function handleItHappened(ItHappened $event): void
    {
        self::$itHappened = true;
    }
}
