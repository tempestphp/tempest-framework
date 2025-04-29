<?php

namespace Tempest\Storage\Config;

use League\Flysystem\FilesystemAdapter;

interface StorageConfig
{
    /**
     * Whether the storage is read-only.
     */
    public bool $readonly {
        get;
        set;
    }

    /**
     * The adapter class.
     */
    public string $adapter {
        get;
    }

    /**
     * Creates the adapter.
     */
    public function createAdapter(): FilesystemAdapter;
}
