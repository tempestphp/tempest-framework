<?php

namespace Tempest\Mail;

use Stringable;

final readonly class Address implements Stringable
{
    public function __construct(
        public string $email,
        public ?string $name = null,
    ) {}

    public function __toString(): string
    {
        if ($this->name) {
            return sprintf('%s <%s>', $this->name, $this->email);
        }

        return $this->email;
    }
}
