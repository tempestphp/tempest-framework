<?php

namespace Tempest\Mail\Exceptions;

use Exception;

final class MailContentWasMissing extends Exception implements MailerException
{
    public function __construct()
    {
        parent::__construct('Either HTML or text content must be provided.');
    }
}
