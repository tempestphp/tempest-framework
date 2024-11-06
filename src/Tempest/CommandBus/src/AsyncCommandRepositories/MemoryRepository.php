<?php

declare(strict_types=1);

namespace Tempest\CommandBus\AsyncCommandRepositories;

use Tempest\CommandBus\AsyncCommandRepository;

final class MemoryRepository implements AsyncCommandRepository
{
    private array $commands = [];

    public function store(string $uuid, object $command): void
    {
        $this->commands[$uuid] = $command;
    }

    public function find(string $uuid): object
    {
        return $this->commands[$uuid];
    }

    public function remove(string $uuid): void
    {
        unset($this->commands[$uuid]);
    }

    public function available(): array
    {
        return array_keys($this->commands);
    }
}
