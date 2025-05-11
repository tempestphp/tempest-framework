<?php

namespace Tempest\Mail\Transports\Ses;

enum Scheme: string
{
    /**
     * Uses Amazon SES's API.
     *
     * @see https://docs.aws.amazon.com/ses/latest/dg/send-email-api.html
     */
    case API = 'ses+api';

    /**
     * Uses Amazon SES's async HTTP transport.
     *
     * @see https://docs.aws.amazon.com/ses/latest/dg/send-email-api.html
     */
    case HTTP = 'ses+https';
}
