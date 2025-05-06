<?php

namespace Tempest\Storage\Config;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\Ftp\FtpConnectionOptions;
use UnitEnum;

final class FTPStorageConfig implements StorageConfig
{
    public string $adapter = FtpAdapter::class;

    public const string WINDOWS = 'windows';

    public const string UNIX = 'UNIX';

    public function __construct(
        public string $host,
        public string $root,
        public string $username,
        public string $password,
        public int $port = 21,
        public bool $ssl = false,
        public bool $utf8 = false,
        public bool $passive = true,
        public int $timeoutInSeconds = 90,
        public int $transferMode = FTP_BINARY,
        public ?string $systemType = null,
        public ?bool $ignorePassiveAddress = null,
        public bool $recurseManually = true,
        public bool $timestampsOnUnixListingsEnabled = false,

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
        return new FtpAdapter(
            connectionOptions: FtpConnectionOptions::fromArray([
                'host' => $this->host,
                'root' => $this->root,
                'username' => $this->username,
                'password' => $this->password,
                'port' => $this->port,
                'ssl' => $this->ssl,
                'timeout' => $this->timeoutInSeconds,
                'utf8' => $this->utf8,
                'passive' => $this->passive,
                'transferMode' => $this->transferMode,
                'systemType' => $this->systemType ?? null,
                'ignorePassiveAddress' => $this->ignorePassiveAddress ?? null,
                'timestampsOnUnixListingsEnabled' => $this->timestampsOnUnixListingsEnabled,
                'recurseManually' => $this->recurseManually,
            ]),
        );
    }
}
