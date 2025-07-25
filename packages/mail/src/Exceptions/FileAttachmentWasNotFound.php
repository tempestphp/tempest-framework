<?php

namespace Tempest\Mail\Exceptions;

use Exception;

final class FileAttachmentWasNotFound extends Exception implements MailerException
{
    public static function forFilesystemFile(string $attachment): self
    {
        return new self("File {$attachment} could not be found on the filesystem.");
    }

    public static function forStorageFile(string $attachment): self
    {
        return new self("File {$attachment} could not be found in the given storage.");
    }
}
