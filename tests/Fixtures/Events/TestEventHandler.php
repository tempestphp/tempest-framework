<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Events;

use Tempest\EventBus\EventHandler;

final class TestEventHandler
{
    public static bool $fromStringEvent = false;

    public static bool $fromEnumEvent = false;

    #[EventHandler('string-event')]
    public function fromString(): void
    {
        self::$fromStringEvent = true;
    }

    #[EventHandler(EnumEvent::Foo)]
    public function fromEnum(): void
    {
        self::$fromEnumEvent = true;
    }
}
