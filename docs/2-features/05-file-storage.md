---
title: File storage
description: "Tempest's storage provides a way to access many different types of filesystems, such as the local filesystem, Amazon S3, Cloudflare R2 or even an FTP server."
---

## Overview

Tempest provides the ability to interact with the local filesystem and many cloud storage solutions, such as Cloudflare R2 or Amazon S3, using the same interface.

This implementation is built on top of [Flysystem](https://github.com/thephpleague/flysystem)—a reliable, battle-tested abstraction layer for file systems.

## Getting started

To get started with file storage, you will first need to create a configuration file for your desired filesystem.

Tempest provides a different configuration object for each provider. For instance, if you wish to interact with an Amazon S3 bucket, you may create a `s3.config.php` file returning an instance of {b`Tempest\Storage\Config\S3StorageConfig`}:

```php app/s3.config.php
return new S3StorageConfig(
    bucket: env('S3_BUCKET'),
    region: env('S3_REGION'),
    accessKeyId: env('S3_ACCESS_KEY_ID'),
    secretAccessKey: env('S3_SECRET_ACCESS_KEY'),
);
```

In this example, the S3 credentials are specified in the `.env`, so a different bucket and credentials can be configured depending on the environment.

Once your storage is configured, you may interact with it by using the {`Tempest\Storage\Storage`} interface. This is usually done through [dependency injection](../1-essentials/05-container.md#injecting-dependencies):

```php app/UserService.php
final readonly class UserService
{
    public function __construct(
        private Storage $storage,
    ) {}

    public function getProfilePictureUrl(User $user): string
    {
        return $this->storage->publicUrl($user->profile_picture_path);
    }

    // …
}
```

## The storage interface

Once you have access to the the {b`Tempest\Storage\Storage`} interface, you gain access to a few useful methods for working with files, directory and streams. All methods are documented, so you are free to explore the source to get an understanding of what you can do with it.

Below are a few useful methods that you may need more often than the others:

```php
/**
 * Gets a public URL to the file at the specified `$location`.
 */
$storage->publicUrl($location);

/**
 * Writes the given `$contents` to the specified `$location`.
 */
$storage->write($location, $contents);

/**
 * Reads the contents of the file at the specified `$location`.
 */
$storage->read($location);

/**
 * Deletes the file or directory at the specified `$location`.
 */
$storage->delete($location);

/**
 * Determines whether a file or a directory exists at the specified `$location`.
 */
$storage->fileOrDirectoryExists($location);
```

## Configuration

Tempest provides a different configuration object for each storage provider. Below are the ones that are currently supported:

- {`Tempest\Storage\Config\LocalStorageConfig`}
- {`Tempest\Storage\Config\R2StorageConfig`}
- {`Tempest\Storage\Config\S3StorageConfig`}
- {`Tempest\Storage\Config\AzureStorageConfig`}
- {`Tempest\Storage\Config\FTPStorageConfig`}
- {`Tempest\Storage\Config\GoogleCloudStorageConfig`}
- {`Tempest\Storage\Config\InMemoryStorageConfig`}
- {`Tempest\Storage\Config\SFTPStorageConfig`}
- {`Tempest\Storage\Config\StorageConfig`}
- {`Tempest\Storage\Config\ZipArchiveStorageConfig`}
- {`Tempest\Storage\Config\CustomStorageConfig`}

### Multiple storages

If you need to work with multiple storage locations, you may create multiple storage configurations using tags. These tags may then be used to resolve the {b`Tempest\Storage\Storage`} interface, which will use the corresponding configuration.

It's a good practice to use an enum for the tag:

```php app/userdata.storage.config.php
return new S3StorageConfig(
    tag: StorageLocation::USER_DATA,
    bucket: env('USERDATA_S3_BUCKET'),
    region: env('USERDATA_S3_REGION'),
    accessKeyId: env('USERDATA_S3_ACCESS_KEY_ID'),
    secretAccessKey: env('USERDATA_S3_SECRET_ACCESS_KEY'),
);
```

```php app/backup.storage.config.php
return new R2StorageConfig(
    tag: StorageLocation::BACKUPS,
    bucket: env('BACKUPS_R2_BUCKET'),
    endpoint: env('BACKUPS_R2_ENDPOINT'),
    accessKeyId: env('BACKUPS_R2_ACCESS_KEY_ID'),
    secretAccessKey: env('BACKUPS_R2_SECRET_ACCESS_KEY'),
);
```

Once you have configured your storages and your tags, you may inject the {b`Tempest\Storage\Storage`} interface using the corresponding tag:

```php app/BackupService.php
final readonly class BackupService
{
    public function __construct(
        #[Tag(StorageLocation::BACKUPS)]
        private Storage $storage,
    ) {}

    // …
}
```

### Read-only storage

A storage may be restricted to only allow read operations. Attempting to write to such a storage will result in a `League\Flysystem\UnableToWriteFile` exception being thrown.

First, the `league/flysystem-read-only` adapter needs to be installed:

```sh
composer require league/flysystem-read-only
```

Once this is done, you may pass the `readonly` parameter to the adapter configuration and set it to `true`.

```php app/data-snapshots.storage.config.php
return new S3StorageConfig(
    tag: StorageLocation::DATA_SNAPSHOTS,
    readonly: true,
    bucket: env('DATA_SNAPSHOTS_S3_BUCKET'),
    region: env('DATA_SNAPSHOTS_S3_REGION'),
    accessKeyId: env('DATA_SNAPSHOTS_S3_ACCESS_KEY_ID'),
    secretAccessKey: env('DATA_SNAPSHOTS_S3_SECRET_ACCESS_KEY'),
);
```

### Custom storage

If you need to implement your own adapter for an unsupported provider, you may do so by implementing the `League\Flysystem\FilesystemAdapter` interface.

Tempest provides a {b`Tempest\Storage\Config\CustomStorageConfig`} configuration object which accepts any `FilesystemAdapter`, which will be resolved through the container.

```php app/custom-storage.config.php
return new CustomStorageConfig(
    adapter: App\MyCustomFilesystemAdapter::class,
);
```

## Testing

By extending {`Tempest\Framework\Testing\IntegrationTest`} from your test case, you gain access to the storage testing utilities through the `storage` property.

These utilities include a way to replace the storage with a testing implementation, as well as a few assertion methods related to files and directories.

### Faking a storage

You may generate a fake, testing-only storage by calling the `fake()` method on the `storage` property. This will replace the storage implementation in the container, and provide useful assertion methods.

```php
// Replace the storage with a fake implementation
$storage = $this->storage->fake();

// Replace the specified storage with a fake implementation
$storage = $this->storage->fake(StorageLocation::DATA_SNAPSHOTS);

// Asserts that the specified file exists
$storage->assertFileExists('file.txt');
```

These fake storages are located in `.tempest/tests/storage`. They get erased every time the `fake()` method is called. To prevent this, you may set the `persist` argument to `true`.

### Preventing storage access during tests

It may be useful to prevent code from using any of the registered storages during tests. This could happen when forgetting to fake a storage for a specific test, for instance, and could result in unexpected costs when relying on a cloud storage provider.

This may be achieved by calling the `preventUsageWithoutFake()` method on the `storage` property.

```php tests/MyServiceTest.php
$this->storage->preventUsageWithoutFake();
```
