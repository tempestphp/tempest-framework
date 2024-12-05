<?php

declare(strict_types=1);

namespace Tempest\Mailer\Components\Address;

final class AddressCollection
{
    use ReadsAddresses;
    use WritesAddresses;

    public function toImmutable(): ImmutableAddressCollection
    {
        return new ImmutableAddressCollection($this->all());
    }
}
