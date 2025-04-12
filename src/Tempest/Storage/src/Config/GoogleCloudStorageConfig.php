<?php

namespace Tempest\Storage\Config;

use Google\Cloud\Storage\StorageClient;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter;

final class GoogleCloudStorageConfig implements StorageConfig
{
    public string $adapter = GoogleCloudStorageAdapter::class;

    public function __construct(
        /**
         * Name of the bucket to use.
         */
        public string $bucket,

        /**
         * The Google Cloud project ID. May be inferred from credentials.
         */
        public ?string $projectId = null,

        /**
         * Absolute path to the JSON file containing the service account credentials.
         */
        public ?string $keyFilePath = null,

        /**
         * May be used to connect to a non-standard GCS endpoint.
         */
        public ?string $apiEndpoint = null,

        /**
         * Prefix to be used for all paths.
         */
        public string $prefix = '',

        /**
         * Less common options.
         */
        public array $options = [],

        /**
         * Whether the storage is read-only.
         */
        public bool $readonly = false,
    ) {}

    public function createAdapter(): FilesystemAdapter
    {
        $client = new StorageClient($this->buildClientConfig());

        return new GoogleCloudStorageAdapter(
            bucket: $client->bucket($this->bucket),
            prefix: $this->prefix,
        );
    }

    private function buildClientConfig(): array
    {
        $config = [];

        if ($this->projectId !== null) {
            $config['projectId'] = $this->projectId;
        }

        if ($this->keyFilePath !== null) {
            $config['keyFilePath'] = $this->keyFilePath;
        }

        if ($this->apiEndpoint !== null) {
            $config['apiEndpoint'] = $this->apiEndpoint;
        }

        if ($this->options !== []) {
            return array_merge($config, $this->options);
        }

        return $config;
    }
}
