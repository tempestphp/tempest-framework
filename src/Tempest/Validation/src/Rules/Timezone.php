<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use DateTimeZone;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class Timezone implements Rule
{
    public function __construct(
        private int $timezoneGroup = DateTimeZone::ALL,
        private ?string $countryCode = null,
    ) {}

    public function isValid(mixed $value): bool
    {
        $timezones = timezone_identifiers_list($this->timezoneGroup, $this->countryCode);

        return in_array($value, $timezones, true);
    }

    public function message(): string
    {
        return 'Value should be a valid timezone';
    }
}
