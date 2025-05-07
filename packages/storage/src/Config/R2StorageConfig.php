<?php

namespace Tempest\Storage\Config;

use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\FilesystemAdapter;
use UnitEnum;

final class R2StorageConfig implements StorageConfig
{
    public string $adapter = AwsS3V3Adapter::class;

    public function __construct(
        /**
         * Name of the bucket.
         */
        public string $bucket,

        /**
         * Endpoint to your S3 storage, without the bucket suffix.
         */
        public string $endpoint,

        /**
         * Access key ID, found in the S3 client compatibility section.
         */
        public string $accessKeyId,

        /**
         * Secret access key, found in the S3 client compatibility section.
         */
        public string $secretAccessKey,

        /**
         * If specified, scope operations to that path.
         */
        public ?string $prefix = null,

        /**
         * Whether the storage is read-only.
         */
        public bool $readonly = false,

        /**
         * Other options.
         */
        public array $options = [],

        /**
         * Identifier for this storage configuration.
         */
        public null|string|UnitEnum $tag = null,
    ) {}

    public function createAdapter(): FilesystemAdapter
    {
        return new S3StorageConfig(
            bucket: $this->bucket,
            region: 'auto',
            endpoint: $this->endpoint,
            accessKeyId: $this->accessKeyId,
            secretAccessKey: $this->secretAccessKey,
            sessionToken: null,
            prefix: $this->prefix,
            readonly: $this->readonly,
            usePathStyleEndpoint: true,
            options: $this->options,
        )->createAdapter();
    }
}
