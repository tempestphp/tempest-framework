<?php

declare(strict_types=1);

namespace Tempest\Mailer\Components\Address;

trait WritesAddresses
{
    /**
     * @var array<Address>
     */
    private array $addresses = [];

    public function add(string|Address $address): self
    {
        $address = $this->normalizeAddress($address);

        if (! in_array($address, $this->addresses, true)) {
            $this->addresses[] = $address;
        }

        return $this;
    }

    public function remove(string|Address $address): self
    {
        $address = $this->normalizeAddress($address);

        $this->addresses = array_filter($this->addresses, static function (Address $value) use ($address) {
            return $value->address !== $address->address;
        });

        return $this;
    }

    private function normalizeAddress(string|Address $address): Address
    {
        return is_string($address) ? new Address($address) : $address;
    }

    /**
     * @param array<Address> $addresses
     */
    abstract public function __construct(array $addresses = []);
}
