<?php

namespace Tempest\Storage\Config;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;

final class LocalStorageConfig implements StorageConfig
{
    public string $adapter = LocalFilesystemAdapter::class;

    public function __construct(
        /**
         * Absolute path to the storage directory.
         */
        public string $path,

        /**
         * Whether the storage is read-only.
         */
        public bool $readonly = false,
    ) {}

    public function createAdapter(): FilesystemAdapter
    {
        return new LocalFilesystemAdapter(
            location: $this->path,
        );
    }
}
