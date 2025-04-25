<?php

namespace Tempest\Storage\Config;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;

final class InMemoryStorageConfig implements StorageConfig
{
    public string $adapter = InMemoryFilesystemAdapter::class;

    public function __construct(
        /**
         * Whether the storage is read-only.
         */
        public bool $readonly = false,
    ) {}

    public function createAdapter(): FilesystemAdapter
    {
        return new InMemoryFilesystemAdapter();
    }
}
