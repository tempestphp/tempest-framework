<?php

namespace Tempest\Storage\Config;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\ZipArchive\FilesystemZipArchiveProvider;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;

final class ZipArchiveStorageConfig implements StorageConfig
{
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
    ) {}

    public function createAdapter(): ZipArchiveAdapter
    {
        return new ZipArchiveAdapter(
            zipArchiveProvider: new FilesystemZipArchiveProvider($this->path),
            root: $this->prefix,
        );
    }
}
