<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Tempest\Validation\HasTranslationVariables;
use Tempest\Validation\Rule;

/**
 * Validates that the value is a valid IPv4 address.
 */
#[Attribute]
final readonly class IsIPv4 implements Rule, HasTranslationVariables
{
    private int $options;

    public function __construct(
        private bool $allowPrivateRange = true,
        private bool $allowReservedRange = true,
    ) {
        $options = FILTER_FLAG_IPV4;

        if (! $this->allowPrivateRange) {
            $options |= FILTER_FLAG_NO_PRIV_RANGE;
        }

        if (! $this->allowReservedRange) {
            $options |= FILTER_FLAG_NO_RES_RANGE;
        }

        $this->options = $options;
    }

    public function isValid(mixed $value): bool
    {
        return boolval(filter_var($value, FILTER_VALIDATE_IP, $this->options));
    }

    public function getTranslationVariables(): array
    {
        return [
            'allow_private_range' => $this->allowPrivateRange,
            'allow_reserved_range' => $this->allowReservedRange,
        ];
    }
}
