<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use libphonenumber\PhoneNumberUtil;
use Tempest\Validation\HasTranslationVariables;
use Tempest\Validation\Rule;
use Throwable;

/**
 * Validates that the value is a valid phone number.
 */
#[Attribute]
final readonly class IsPhoneNumber implements Rule, HasTranslationVariables
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

    public function getTranslationVariables(): array
    {
        return [
            'default_region' => $this->defaultRegion,
        ];
    }
}
