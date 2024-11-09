<?php

declare(strict_types=1);

namespace Tempest\CommandBus\AsyncCommandRepositories;

use Tempest\CommandBus\CommandRepository;

final class MemoryRepository implements CommandRepository
{
    private array $commands = [];

    public function store(string $uuid, object $command): void
    {
        $this->commands[$uuid] = $command;
    }

    public function getPendingCommands(): array
    {
        return $this->commands;
    }

    public function findPendingCommand(string $uuid): object
    {
        return $this->commands[$uuid];
    }

    public function markAsDone(string $uuid): void
    {
        unset($this->commands[$uuid]);
    }

    public function markAsFailed(string $uuid): void
    {
        unset($this->commands[$uuid]);
    }
}
