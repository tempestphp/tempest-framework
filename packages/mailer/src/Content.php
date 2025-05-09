<?php

namespace Tempest\Mail;

use Tempest\Mail\Exceptions\MissingContentException;
use Tempest\View\View;

/**
 * Represents the content of an {@see \Tempest\Mailer\Envelope}.
 */
final class Content
{
    public function __construct(
        public null|string|View $html = null,
        public ?string $text = null,
        /** @var Attachment[] */
        public array $attachments = [],
    ) {
        if (! $text && ! $html) {
            throw new MissingContentException();
        }
    }
}
