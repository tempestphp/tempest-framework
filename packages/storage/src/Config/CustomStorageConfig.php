<?php

namespace Tempest\Storage\Config;

use League\Flysystem\FilesystemAdapter;
use UnitEnum;

use function Tempest\Container\get;

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

        /**
         * Identifier for this storage configuration.
         */
        public null|string|UnitEnum $tag = null,
    ) {}

    public function createAdapter(): FilesystemAdapter
    {
        return get($this->adapter);
    }
}
