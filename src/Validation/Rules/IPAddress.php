<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class IPAddress implements Rule
{
    private int $options;

    public function __construct(
        private bool $ipv4 = false,
        private bool $ipv6 = false,
        private bool $allowPrivateRange = true,
        private bool $allowReservedRange = true
    ) {
        $options = 0;

        if ($this->ipv4) {
            $options = $options | FILTER_FLAG_IPV4;
        }

        if ($this->ipv6) {
            $options = $options | FILTER_FLAG_IPV6;
        }

        if (! $this->allowPrivateRange) {
            $options = $options | FILTER_FLAG_NO_PRIV_RANGE;
        }

        if (! $this->allowReservedRange) {
            $options = $options | FILTER_FLAG_NO_RES_RANGE;
        }

        $this->options = $options;
    }

    public function isValid(mixed $value): bool
    {
        return boolval(filter_var($value, FILTER_VALIDATE_IP, $this->options));
    }

    public function message(): string
    {
        if ($this->options === 0) {
            return 'Value should be a valid IP address';
        }

        $version = 'IP';
        $additions = [];

        if ($this->options & FILTER_FLAG_IPV4 && ! ($this->options & FILTER_FLAG_IPV6)) {
            $version = 'IPv4';
        }

        if ($this->options & FILTER_FLAG_IPV6 && ! ($this->options & FILTER_FLAG_IPV4)) {
            $version = 'IPv6';
        }

        if ($this->options & FILTER_FLAG_NO_PRIV_RANGE) {
            $additions[] = 'not in a private range';
        }

        if ($this->options & FILTER_FLAG_NO_RES_RANGE) {
            $additions[] = 'not in a reserved range';
        }

        return "Value should be a valid $version address" . (
            empty($additions) ? '' : ' that is ' . implode(' and ', $additions)
        );
    }
}
