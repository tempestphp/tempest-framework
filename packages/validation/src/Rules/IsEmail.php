<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\EmailValidation;
use Egulias\EmailValidator\Validation\RFCValidation;
use Tempest\Validation\Rule;

/**
 * Validates that the value is a valid email address.
 */
#[Attribute]
final readonly class IsEmail implements Rule
{
    public function __construct(
        private EmailValidation $validationMethod = new RFCValidation(),
    ) {}

    public function isValid(mixed $value): bool
    {
        $emailValidator = new EmailValidator();

        return $emailValidator->isValid($value ?? '', $this->validationMethod);
    }
}
