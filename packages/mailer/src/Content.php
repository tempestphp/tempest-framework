<?php

namespace Tempest\Mail;

use Tempest\Mail\Attachment;
use Tempest\Mail\Exceptions\MailContentWasMissing;
use Tempest\View\View;

/**
 * Represents the content of an {@see \Tempest\Mailer\Envelope}.
 */
final class Content
{
    /**
     * @param Attachment[] $attachments
     */
    public function __construct(
        public null|string|View $html = null,
        public ?string $text = null,
        public array $attachments = [],
    ) {
        if (! $text && ! $html) {
            throw new MailContentWasMissing();
        }
    }
}
