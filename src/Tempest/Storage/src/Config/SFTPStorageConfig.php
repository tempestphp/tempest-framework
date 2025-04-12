<?php

namespace Tempest\Storage\Config;

use League\Flysystem\PhpseclibV3\SftpAdapter;
use League\Flysystem\PhpseclibV3\SftpConnectionProvider;

final class SFTPStorageConfig implements StorageConfig
{
    public function __construct(
        public string $host,
        public string $root,
        public string $username,
        public string $password,
        public ?string $privateKey = null,
        public ?string $passphrase = null,
        public int $port = 22,
        public bool $useAgent = false,
        public int $timeoutInSeconds = 10,
        public int $maxTries = 3,
        public ?string $hostFingerprint = null,
        public bool $readonly = false,
    ) {}

    public function createAdapter(): SftpAdapter
    {
        return new SftpAdapter(
            connectionProvider: new SftpConnectionProvider(
                host: $this->host,
                username: $this->username,
                password: $this->password,
                privateKey: $this->privateKey,
                passphrase: $this->passphrase,
                port: $this->port,
                useAgent: false,
                timeout: $this->timeoutInSeconds,
                maxTries: $this->maxTries,
                hostFingerprint: $this->hostFingerprint,
            ),
            root: $this->root,
        );
    }
}
