<?php

namespace Tempest\Storage\Config;

use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;
use League\Flysystem\FilesystemAdapter;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

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
    ) {}

    public function createAdapter(): FilesystemAdapter
    {
        return new AzureBlobStorageAdapter(
            client: BlobRestProxy::createBlobService($this->dsn),
            container: $this->container,
            prefix: $this->prefix,
        );
    }
}
