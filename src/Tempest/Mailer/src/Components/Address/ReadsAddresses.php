<?php

declare(strict_types=1);

namespace Tempest\Mailer\Components\Address;

trait ReadsAddresses
{
    /**
     * @var array<Address>
     */
    private array $addresses = [];

    /**
     * @param array<Address> $addresses
     */
    public function __construct(array $addresses = [])
    {
        $this->addresses = $addresses;
    }

    public function all(): array
    {
        return $this->addresses;
    }
}
