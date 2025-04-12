<?php

namespace Tempest\Storage\Config;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\FilesystemAdapter;

final class S3StorageConfig implements StorageConfig
{
    public string $adapter = AwsS3V3Adapter::class;

    public function __construct(
        /**
         * Name of the bucket.
         */
        public string $bucket,

        /**
         * AWS region for the bucket (e.g., 'us-east-1', 'eu-west-1').
         */
        public string $region,

        /**
         * AWS access key ID. If null, the SDK will attempt to use the default credential provider chain (env vars, config files, IAM role).
         */
        public ?string $accessKeyId = null,

        /**
         * AWS secret cccess key. If null, the SDK will attempt to use the default credential provider chain.
         */
        public ?string $secretAccessKey = null,

        /**
         * AWS session token (typically used with temporary credentials). If null, the SDK will attempt to use the default credential provider chain.
         */
        public ?string $sessionToken = null,

        /**
         * If specified, scope operations to that path.
         */
        public ?string $prefix = null,

        /**
         * Whether the storage is read-only.
         */
        public bool $readonly = false,

        /**
         * Optional custom endpoint URL (e.g., for S3-compatible storage like R2).
         */
        public ?string $endpoint = null,

        /**
         * Set to true for S3-compatible storage that requires path-style addressing (e.g., http://localhost:9000/bucket/key). Defaults to false (virtual hosted-style: http://bucket.localhost:9000/key).
         */
        public bool $usePathStyleEndpoint = false,

        /**
         * Other options.
         */
        public array $options = [],
    ) {}

    public function createAdapter(): FilesystemAdapter
    {
        return new AwsS3V3Adapter(
            client: new S3Client($this->buildClientConfig()),
            bucket: $this->bucket,
            prefix: $this->prefix ?? '',
        );
    }

    private function buildClientConfig(): array
    {
        $config = [
            'region' => $this->region,
        ];

        if ($this->accessKeyId !== null && $this->secretAccessKey !== null) {
            $config['credentials'] = [
                'key' => $this->accessKeyId,
                'secret' => $this->secretAccessKey,
            ];

            if ($this->sessionToken !== null) {
                $config['credentials']['token'] = $this->sessionToken;
            }
        }

        if ($this->endpoint !== null) {
            $config['endpoint'] = $this->endpoint;
            $config['use_path_style_endpoint'] = $this->usePathStyleEndpoint;
        }

        if ($this->options !== []) {
            return array_merge($config, $this->options);
        }

        return $config;
    }
}
