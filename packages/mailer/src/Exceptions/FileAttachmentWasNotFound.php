<?php

namespace Tempest\Mail\Exceptions;

use Exception;
use UnitEnum;

final class FileAttachmentWasNotFound extends Exception implements MailerException
{
    public static function forFilesystemFile(string $attachment): self
    {
        return new self("File {$attachment} could not be found on the filesystem.");
    }

    public static function forStorageFile(string $attachment, null|string|UnitEnum $tag): self
    {
        $name = static::resolveStorageTag($tag);

        if (is_null($name)) {
            return new self("File {$attachment} could not be found in the storage.");
        }

        return new self("File {$attachment} could not be found in the storage {$name}.");
    }

    public static function storageDoesNotExist(null|string|UnitEnum $tag): self
    {
        $name = static::resolveStorageTag($tag);

        if (is_null($name)) {
            return new self('Storage is not registered.');
        }

        return new self("Storage {$name} is not registered.");
    }

    private static function resolveStorageTag(null|string|UnitEnum $name): ?string
    {
        if (is_null($name)) {
            return null;
        }

        return is_string($name)
            ? $name
            : $name->name;
    }
}
