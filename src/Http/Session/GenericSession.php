<?php

namespace Tempest\Http\Session;

final class GenericSession implements Session
{
    private bool $isStarted = false;

    public function __construct(private readonly SessionStorageDriver $driver)
    {}

    public function start(): void
    {
        if ($this->isStarted) {
            return;
        }

        $this->driver->start();
    }

    public function save(): void
    {
        // TODO: Implement
        $this->driver->save();
    }
}