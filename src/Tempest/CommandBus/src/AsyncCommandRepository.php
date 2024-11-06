<?php

namespace Tempest\CommandBus;

interface AsyncCommandRepository
{
    public function store(string $uuid, object $command): void;

    public function find(string $uuid): object;

    public function remove(string $uuid): void;

    /** @return string[] */
    public function all(): array;
}