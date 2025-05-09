<?php

namespace Tempest\Mailer;

use Stringable;

final readonly class Email
{
    public function __construct(
        public string|Stringable|null $from,
        public string|Stringable|array $to,
        public string|Stringable $subject,
        public string|Stringable $body,
        public array $attachments = [],
        public bool $async = false,
    ) {}
}
