<?php

namespace Tempest\Mail\Exceptions;

use Exception;

final class SendingMailWasForbidden extends Exception implements MailerException
{
    public function __construct()
    {
        parent::__construct('Mailer is being used without a testing fake.');
    }
}
