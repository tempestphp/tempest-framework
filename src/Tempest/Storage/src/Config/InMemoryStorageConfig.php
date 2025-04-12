<?php

namespace Tempest\Storage\Config;

use League\Flysystem\InMemory\InMemoryFilesystemAdapter;

final class InMemoryStorageConfig implements StorageConfig
{
    public function __construct(
        /**
         * Whether the storage is read-only.
         */
        public bool $readonly = false,
    ) {}

    public function createAdapter(): InMemoryFilesystemAdapter
    {
        return new InMemoryFilesystemAdapter();
    }
}
