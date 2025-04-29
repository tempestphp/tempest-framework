<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use libphonenumber\PhoneNumberUtil;
use Tempest\Validation\Rule;
use Throwable;

#[Attribute]
final readonly class PhoneNumber implements Rule
{
    public function __construct(
        private ?string $defaultRegion = null,
    ) {}

    public function isValid(mixed $value): bool
    {
        try {
            $phoneNumberUtil = PhoneNumberUtil::getInstance();
            $phoneNumber = $phoneNumberUtil->parse($value, $this->defaultRegion);

            return $phoneNumberUtil->isValidNumber($phoneNumber);
        } catch (Throwable) {
            return false;
        }
    }

    public function message(): string
    {
        if (! $this->defaultRegion) {
            return 'Value should be a valid phone number';
        }

        return sprintf(
            'Value should be a valid %s phone number',
            strtoupper($this->defaultRegion),
        );
    }
}
