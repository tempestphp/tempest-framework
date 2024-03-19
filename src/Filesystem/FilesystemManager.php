<?php

namespace Tempest\Filesystem;

final class FilesystemManager
{
    /**
     * @var array<string,Filesystem>
     */
    private array $filesystems = [];

    public function get(string $name): Filesystem
    {
        // TODO: Update
        return $this->filesystems[$name] ?? throw new \RuntimeException();
    }

    public function set(string $name, Filesystem $filesystem): self
    {
        $this->filesystems[$name] = $filesystem;

        return $this;
    }
}