<?php

namespace Tempest\Storage;

use Exception;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;
use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use League\Flysystem\PhpseclibV3\SftpAdapter;
use League\Flysystem\ReadOnly\ReadOnlyFilesystemAdapter;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;

final class AdapterWasMissing extends Exception implements StorageException
{
    public function __construct(
        private readonly string $missing,
    ) {
        $packageName = $this->getPackageName();
        $message = $packageName
            ? sprintf('The `%s` adapter is missing. Install it using `composer require %s`.', $missing, $packageName)
            : sprintf('The `%s` adapter is missing.', $missing);

        parent::__construct($message);
    }

    private function getPackageName(): ?string
    {
        return match ($this->missing) {
            AwsS3V3Adapter::class => 'league/flysystem-aws-s3-v3',
            InMemoryFilesystemAdapter::class => 'league/flysystem-memory',
            ReadOnlyFilesystemAdapter::class => 'league/flysystem-read-only',
            SftpAdapter::class => 'league/flysystem-sftp',
            ZipArchiveAdapter::class => 'league/flysystem-ziparchive',
            AzureBlobStorageAdapter::class => 'league/flysystem-azure-blob-storage',
            FtpAdapter::class => 'league/flysystem-ftp',
            GoogleCloudStorageAdapter::class => 'league/flysystem-google-cloud-storage',
            default => null,
        };
    }
}
