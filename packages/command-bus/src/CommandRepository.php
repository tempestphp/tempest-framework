<?php

declare(strict_types=1);

namespace Tempest\CommandBus;

interface CommandRepository
{
    public function store(string $uuid, object $command): void;

    /** @return array<string, object> */
    public function getPendingCommands(): array;

    public function findPendingCommand(string $uuid): object;

    public function markAsDone(string $uuid): void;

    public function markAsFailed(string $uuid): void;
}
