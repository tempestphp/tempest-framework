<?php

namespace Tempest\Storage\Config;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use UnitEnum;

final class InMemoryStorageConfig implements StorageConfig
{
    public string $adapter = InMemoryFilesystemAdapter::class;

    public function __construct(
        /**
         * Whether the storage is read-only.
         */
        public bool $readonly = false,

        /**
         * Identifier for this storage configuration.
         */
        public null|string|UnitEnum $tag = null,
    ) {}

    public function createAdapter(): FilesystemAdapter
    {
        return new InMemoryFilesystemAdapter();
    }
}
