<?php

declare(strict_types=1);

namespace Tempest\CommandBus;

interface CommandRepository
{
    public function store(string $uuid, object $command): void;

    public function find(string $uuid): object;

    public function markAsDone(string $uuid): void;

    public function markAsFailed(string $uuid): void;

    /** @return string[] */
    public function getPendingUuids(): array;
}
