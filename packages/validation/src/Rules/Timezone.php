<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use DateTimeZone;
use Tempest\Validation\Rule;

#[Attribute]
final readonly class Timezone implements Rule
{
    /**
     * Ensures that the value is a valid timezone, optionally filtered by a specific timezone group or country code.
     *
     * @param int $timezoneGroup A {@see \DateTimeZone} group.
     * @param string|null $countryCode A two-letter ISO 3166-1 compatible country code. Only used when filtering per country.
     */
    public function __construct(
        private int $timezoneGroup = DateTimeZone::ALL,
        private ?string $countryCode = null,
    ) {}

    public function isValid(mixed $value): bool
    {
        return in_array(
            needle: $value,
            haystack: timezone_identifiers_list($this->timezoneGroup, $this->countryCode),
            strict: true,
        );
    }
}
