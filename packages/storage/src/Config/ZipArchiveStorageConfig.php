<?php

namespace Tempest\Storage\Config;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\ZipArchive\FilesystemZipArchiveProvider;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;
use UnitEnum;

final class ZipArchiveStorageConfig implements StorageConfig
{
    public string $adapter = ZipArchiveAdapter::class;

    public function __construct(
        /**
         * Absolute path to the zip file.
         */
        public string $path,

        /**
         * Prefix to be used for all paths in the zip archive.
         */
        public string $prefix = '',

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
        return new ZipArchiveAdapter(
            zipArchiveProvider: new FilesystemZipArchiveProvider($this->path),
            root: $this->prefix,
        );
    }
}
