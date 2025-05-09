<?php

namespace Tempest\Mail\Exceptions;

use Exception;

final class MissingExpeditorAddressException extends Exception implements MailerException
{
    public function __construct()
    {
        parent::__construct('No expeditor address provided.');
    }
}
