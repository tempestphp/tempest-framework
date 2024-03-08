<?php

namespace Tempest\Http\Session;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Events\EventHandler;

final readonly class CleanupSessionsCommand
{
    public function __construct(
        private Console $console,
        private SessionManager $sessionManager,
    ) {}

    #[ConsoleCommand(
        name: 'session:clean',
        description: 'Find and remove all expired sessions',
    )]
    public function __invoke(): void
    {
        $this->console->info('Cleaning up sessions...');

        $this->sessionManager->cleanup();

        $this->console->success('Done');
    }

    #[EventHandler]
    public function onSessionDestroyed(SessionDestroyed $event): void
    {
        $this->console->info("\t- {$event->id} removed");
    }
}