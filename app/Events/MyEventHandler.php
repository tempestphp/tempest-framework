<?php

declare(strict_types=1);

namespace App\Events;

use Tempest\Events\EventHandler;

final class MyEventHandler
{
    public static bool $itHappened = false;

    #[EventHandler]
    public function handleItHappened(ItHappened $event): void
    {
        self::$itHappened = true;
    }
}
