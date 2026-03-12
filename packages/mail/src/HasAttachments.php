<?php

namespace Tempest\Mail;

interface HasAttachments
{
    /**
     * @var \Tempest\Mail\Attachment[] $attachments
     */
    public array $attachments {
        get;
    }
}
