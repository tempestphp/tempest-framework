<?php

declare(strict_types=1);

namespace Tempest\CommandBus;

interface AsyncCommandRepository
{
    public function store(string $uuid, object $command): void;

    public function find(string $uuid): object;

    public function remove(string $uuid): void;

    /** @return string[] */
    public function available(): array;
}
