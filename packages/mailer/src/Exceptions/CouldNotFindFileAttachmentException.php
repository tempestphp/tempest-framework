<?php

namespace Tempest\Mail\Exceptions;

use Exception;

final class CouldNotFindFileAttachmentException extends Exception implements MailerException
{
    public function __construct(string $file)
    {
        parent::__construct(sprintf('File `%s` could not be found on the filesystem.', $file));
    }
}
