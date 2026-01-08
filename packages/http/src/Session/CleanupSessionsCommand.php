<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Schedule;
use Tempest\Console\Scheduler\Every;
use Tempest\EventBus\EventBus;

final readonly class CleanupSessionsCommand
{
    public function __construct(
        private Console $console,
        private SessionManager $sessionManager,
        private EventBus $eventBus,
    ) {}

    #[ConsoleCommand(name: 'session:clean', description: 'Finds and removes all expired sessions', aliases: ['session:cleanup'])]
    #[Schedule(Every::MINUTE)]
    public function __invoke(): void
    {
        $this->eventBus->listen($this->onSessionDeleted(...));
        $this->sessionManager->deleteExpiredSessions();
    }

    private function onSessionDeleted(SessionDeleted $event): void
    {
        $this->console->keyValue((string) $event->id, "<style='bold fg-green'>DESTROYED</style>");
    }
}
