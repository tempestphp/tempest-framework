<?php

namespace Tempest\Storage\Config;

use League\Flysystem\FilesystemAdapter;

use function Tempest\get;

final class CustomStorageConfig implements StorageConfig
{
    public function __construct(
        /**
         * FQCN of the custom Flysystem adapter, resolved through the container.
         *
         * @var class-string<FilesystemAdapter>
         */
        public string $adapter,

        /**
         * Whether the storage is read-only.
         */
        public bool $readonly = false,
    ) {}

    public function createAdapter(): FilesystemAdapter
    {
        return get($this->adapter);
    }
}
