<?php

namespace Tempest\Mail\Transports;

use Tempest\Mail\EmailAddress;

interface ProvidesDefaultSender
{
    /**
     * The default address from which emails will be sent.
     */
    public null|string|EmailAddress $defaultSender {
        get;
    }
}
