<?php

namespace Tempest\Mail\Exceptions;

use Exception;

final class SenderWasMissing extends Exception implements MailerException
{
    public function __construct()
    {
        parent::__construct('No sender address provided.');
    }
}
