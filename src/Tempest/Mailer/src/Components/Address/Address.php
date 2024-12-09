<?php

declare(strict_types=1);

namespace Tempest\Mailer\Components\Address;

final readonly class Address
{
    public function __construct(
        public string $address,
        public ?string $name = null,
    ) {
    }
}
