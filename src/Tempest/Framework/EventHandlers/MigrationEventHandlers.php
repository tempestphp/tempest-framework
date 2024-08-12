<?php

namespace Tempest\Framework\EventHandlers;

use Tempest\Console\Console;
use Tempest\Database\Migrations\MigrationFailed;
use Tempest\EventBus\EventHandler;

final readonly class MigrationEventHandlers
{
    public function __construct(private Console $console) {}

    #[EventHandler]
    public function onMigrationFailed(MigrationFailed $event): void
    {
        $this->console->error(sprintf("Error while executing migration: %s", $event->name ?? 'command'));
        $this->console->error($event->exception->getMessage());
    }
}