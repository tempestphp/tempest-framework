<?php

namespace Tempest\Mail\Exceptions;

use Exception;

final class MissingRecipientAddressException extends Exception implements MailerException
{
    public function __construct()
    {
        parent::__construct('No recipient address provided.');
    }
}
