<?php

declare(strict_types=1);

namespace Tempest\Filesystem;

final class Filesystem
{
    private Driver $driver;

    public function __construct(Driver $driver)
    {
        $this->setDriver($driver);
    }

    public function getDriver(): Driver
    {
        return $this->driver;
    }

    public function setDriver(Driver $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    public function read(string $location): string
    {
        return $this->driver->read($location);
    }

    public function write(string $location, string $content, bool $overwrite = false): void
    {
        $this->driver->write($location, $content);
    }

    public function isFile(string $location): bool
    {
        return $this->driver->isFile($location);
    }

    public function isDirectory(string $location): bool
    {
        return $this->driver->isDirectory($location);
    }

    public function exists(string $location): bool
    {
        return $this->isFile($location) || $this->isDirectory($location);
    }

    public function createDirectory(string $location): void
    {
        $this->driver->createDirectory($location);
    }

    public function deleteDirectory(string $location): void
    {
        $this->driver->deleteDirectory($location);
    }

    public function createStream(string $location): Stream
    {
        return $this->driver->createStream($location);
    }
}
