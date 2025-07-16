<?php

namespace Tempest\Mail\Transports;

use Tempest\Mail\Address;

interface ProvidesDefaultSender
{
    /**
     * The default address from which emails will be sent.
     */
    public null|string|Address $defaultSender {
        get;
    }
}
