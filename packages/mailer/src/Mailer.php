<?php

namespace Tempest\Mail;

/**
 * Responsible for sending emails.
 */
interface Mailer
{
    /**
     * Sends the given email.
     */
    public function send(Email $email): SentEmail;
}
