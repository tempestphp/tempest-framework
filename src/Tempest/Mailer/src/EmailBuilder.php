<?php

declare(strict_types=1);

namespace Tempest\Mailer;

use Tempest\Mailer\Components\Address\Address;
use Tempest\Mailer\Components\Address\AddressCollection;

final class EmailBuilder
{
    private(set) public AddressCollection $to;

    private(set) public AddressCollection $cc;

    private(set) public AddressCollection $bcc;

    private(set) public string $subject;

    public function __construct()
    {
        $this->to = new AddressCollection();
        $this->cc = new AddressCollection();
        $this->bcc = new AddressCollection();
    }

    public function to(Address|string $to): self
    {
        $this->to->add($to);

        return $this;
    }

    public function cc(Address|string $cc): self
    {
        $this->cc->add($cc);

        return $this;
    }

    public function bcc(Address|string $bcc): self
    {
        $this->bcc->add($bcc);

        return $this;
    }

    public function subject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function make(): Email
    {
        return new Email(
            recipients: $this->to->toImmutable(),
            cc: $this->cc->toImmutable(),
            bcc: $this->bcc->toImmutable(),
            subject: $this->subject,
        );
    }
}
