<?php

namespace Tempest\Mailer;

final class MailConfig
{
    public ?string $from;

    public function __construct(
        ?string $from = null,
    ) {
        $this->from = $from ?? env('MAIL_FROM');
    }
}