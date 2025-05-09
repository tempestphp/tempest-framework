<?php

namespace Tempest\Mail;

interface Attachment
{
    /**
     * Resolves the attachment to raw data.
     */
    public \Closure $resolve {
        get;
    }

    /**
     * File name of the attachment.
     */
    public ?string $name {
        get;
    }

    /**
     * Content type of the attachment.
     *
     * TODO: enum?
     */
    public ?string $contentType {
        get;
    }
}
