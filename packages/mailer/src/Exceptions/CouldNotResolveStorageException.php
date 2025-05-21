<?php

namespace Tempest\Mail\Exceptions;

use Exception;

final class CouldNotResolveStorageException extends Exception implements MailerException
{
    public function __construct()
    {
        parent::__construct('Could not resolve the storage from the container.');
    }
}
