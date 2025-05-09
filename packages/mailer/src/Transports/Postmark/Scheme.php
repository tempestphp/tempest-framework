<?php

namespace Tempest\Mail\Transports\Postmark;

enum Scheme: string
{
    /**
     * Use Postmark's API.
     *
     * @see https://postmarkapp.com/developer/user-guide/send-email-with-api
     */
    case API = 'postmark+api';

    /**
     * Use Postmark's SMTP service.
     *
     * @see https://postmarkapp.com/smtp-service
     */
    case SMTP = 'postmark+smtp';
}
