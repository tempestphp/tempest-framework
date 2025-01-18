<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class IPv6 implements Rule
{
    private int $options;

    public function __construct(
        private bool $allowPrivateRange = true,
        private bool $allowReservedRange = true,
    ) {
        $options = 0;
        $options = $options | FILTER_FLAG_IPV6;

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
        /**
         * Fix for Windows
         *
         * @see https://github.com/tempestphp/tempest-framework/actions/runs/12807071926/job/35706856702?pr=884
         */
        if ($this->options & FILTER_FLAG_NO_RES_RANGE && $this->isReservedIPv6($value)) {
            return false;
        }

        return boolval(filter_var($value, FILTER_VALIDATE_IP, $this->options));
    }

    public function message(): string
    {
        if ($this->options & FILTER_FLAG_NO_PRIV_RANGE) {
            $additions[] = 'not in a private range';
        }

        if ($this->options & FILTER_FLAG_NO_RES_RANGE) {
            $additions[] = 'not in a reserved range';
        }

        return 'Value should be a valid IPv6 address' . (
            empty($additions) ? '' : ' that is ' . implode(' and ', $additions)
        );
    }

    private function isReservedIPv6(string $ipv6): bool
    {
        $ipBin  = inet_pton($ipv6);

        return $ipBin !== false && str_starts_with(inet_pton('2001:db8::'), substr($ipBin, 0, 4));
    }
}
