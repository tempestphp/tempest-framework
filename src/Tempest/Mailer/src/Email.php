<?php

declare(strict_types=1);

namespace Tempest\Mailer;

use Tempest\Mailer\Components\Address\ImmutableAddressCollection;

final readonly class Email
{
    public function __construct(
        public ImmutableAddressCollection $recipients,
        public ImmutableAddressCollection $cc,
        public ImmutableAddressCollection $bcc,
        public string $subject,
    ) {
    }
}
