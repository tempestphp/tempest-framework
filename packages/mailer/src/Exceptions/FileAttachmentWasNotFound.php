<?php

namespace Tempest\Mail\Exceptions;

use Exception;

final class FileAttachmentWasNotFound extends Exception implements MailerException
{
    public function __construct(
        public readonly string $file,
    ) {
        parent::__construct("File {$file} could not be found on the filesystem.");
    }
}
