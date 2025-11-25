<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Events;

use Tempest\EventBus\EventHandler;

final class TestEventHandler
{
    public static bool $fromStringEvent = false;

    public static bool $fromEnumEvent = false;

    public static int $onceCount = 0;

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

    #[EventHandler]
    public function onceEventA(OnceEvent $event): void
    {
        self::$onceCount += 1;
    }

    #[EventHandler]
    public function onceEventB(OnceEvent $event): void
    {
        self::$onceCount += 1;
    }
}
