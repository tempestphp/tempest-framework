<?php

namespace Tempest\Storage\Config;

use AzureOss\FlysystemAzureBlobStorage\AzureBlobStorageAdapter;
use AzureOss\Storage\Blob\BlobServiceClient;
use League\Flysystem\FilesystemAdapter;
use UnitEnum;

final class AzureStorageConfig implements StorageConfig
{
    public string $adapter = AzureBlobStorageAdapter::class;

    public function __construct(
        /**
         * Connection string to the Azure Blob Storage account.
         */
        public string $dsn,

        /**
         * Name of the container to use.
         */
        public string $container,

        /**
         * Prefix to be used for all paths.
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
        return new AzureBlobStorageAdapter(
            containerClient: BlobServiceClient::fromConnectionString($this->dsn)->getContainerClient($this->container),
            prefix: $this->prefix,
        );
    }
}
