<?php

namespace Tests\Tempest\Integration\Validator;

use PHPUnit\Framework\Attributes\TestWith;
use Tempest\DateTime\FormatPattern;
use Tempest\Validation\Rule;
use Tempest\Validation\Rules;
use Tempest\Validation\Validator;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class TranslationsTest extends FrameworkIntegrationTestCase
{
    private function translate(Rule $rule, ?string $field = null): string
    {
        return $this->container
            ->get(Validator::class)
            ->getErrorMessage($rule, $field);
    }

    private function formatWithField(string $message, ?string $field, string $expected = 'Value'): string
    {
        return sprintf($message, $field ?? $expected);
    }

    #[TestWith([null])]
    #[TestWith(['Creation date'])]
    public function test_after_date(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be after or equal to January 1, 2024', $field, expected: 'Date'),
            actual: $this->translate(new Rules\AfterDate(date: '2024-01-01', inclusive: true), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be after January 1, 2024', $field, expected: 'Date'),
            actual: $this->translate(new Rules\AfterDate(date: '2024-01-01', inclusive: false), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Creation date'])]
    public function test_before_date(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a date before or equal to January 1, 2024', $field, expected: 'Date'),
            actual: $this->translate(new Rules\BeforeDate(date: '2024-01-01', inclusive: true), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be a date before January 1, 2024', $field, expected: 'Date'),
            actual: $this->translate(new Rules\BeforeDate(date: '2024-01-01', inclusive: false), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Creation date'])]
    public function test_between_dates(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a date between January 1, 2024 and January 1, 2025, included', $field, expected: 'Date'),
            actual: $this->translate(new Rules\BetweenDates(first: '2024-01-01', second: '2025-01-01', inclusive: true), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be a date between January 1, 2024 and January 1, 2025', $field, expected: 'Date'),
            actual: $this->translate(new Rules\BetweenDates(first: '2024-01-01', second: '2025-01-01'), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_alpha(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must contain only alphabetic characters', $field),
            actual: $this->translate(new Rules\Alpha(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_alpha_numeric(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must contain only alphanumeric characters', $field),
            actual: $this->translate(new Rules\AlphaNumeric(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_array_list(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a list', $field),
            actual: $this->translate(new Rules\ArrayList(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_between(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be between 0 and 2', $field, expected: 'Number'),
            actual: $this->translate(new Rules\Between(min: 0, max: 2), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be between 2 and 4', $field, expected: 'Number'),
            actual: $this->translate(new Rules\Between(min: 2, max: 4), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Array'])]
    public function test_count(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must have at least 3 items', $field),
            actual: $this->translate(new Rules\Count(min: 3), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must have at least 0 items', $field),
            actual: $this->translate(new Rules\Count(min: 0), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must have at most 10 items', $field),
            actual: $this->translate(new Rules\Count(max: 10), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must have between 3 and 10 items', $field),
            actual: $this->translate(new Rules\Count(min: 3, max: 10), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must have between 0 and 2 items', $field),
            actual: $this->translate(new Rules\Count(min: 0, max: 2), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_date_time_format(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must use the format yyyy-MM-dd', $field),
            actual: $this->translate(new Rules\DateTimeFormat('yyyy-MM-dd'), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must use the format MM/dd/yyyy', $field),
            actual: $this->translate(new Rules\DateTimeFormat(FormatPattern::AMERICAN), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_divisible_by(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be divisible by 1', $field, expected: 'Number'),
            actual: $this->translate(new Rules\DivisibleBy(divisor: 1), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_does_not_end_with(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must not end with "foo"', $field),
            actual: $this->translate(new Rules\DoesNotEndWith(needle: 'foo'), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_email(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a valid email address', $field, expected: 'Email'),
            actual: $this->translate(new Rules\Email(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_does_not_start_with(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must not start with "foo"', $field),
            actual: $this->translate(new Rules\DoesNotStartWith(needle: 'foo'), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_ends_with(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must end with "foo"', $field),
            actual: $this->translate(new Rules\EndsWith(needle: 'foo'), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_even(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be even', $field, expected: 'Number'),
            actual: $this->translate(new Rules\Even(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_hex_color(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a hexadecimal color', $field),
            actual: $this->translate(new Rules\HexColor(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_in(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a', $field),
            actual: $this->translate(new Rules\In(['a']), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be a or b', $field),
            actual: $this->translate(new Rules\In(['a', 'b']), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be a, b or c', $field),
            actual: $this->translate(new Rules\In(['a', 'b', 'c']), field: $field),
        );
    }

    #[TestWith(['IP', null])]
    #[TestWith(['IP', 'Input'])]
    #[TestWith(['IPv4', null])]
    #[TestWith(['IPv4', 'Input'])]
    #[TestWith(['IPv6', null])]
    #[TestWith(['IPv6', 'Input'])]
    public function test_ip(string $ip, ?string $field = null): void
    {
        $class = match ($ip) {
            'IPv4' => Rules\IPv4::class,
            'IPv6' => Rules\IPv6::class,
            default => Rules\IP::class,
        };

        $this->assertSame(
            expected: $this->formatWithField("%s must be a valid {$ip} address", $field),
            actual: $this->translate(new $class(), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField("%s must be a valid {$ip} address, not in a reserved range", $field),
            actual: $this->translate(new $class(allowReservedRange: false), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField("%s must be a valid {$ip} address, not in a private range", $field),
            actual: $this->translate(new $class(allowPrivateRange: false), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField("%s must be a valid {$ip} address, not in a private or reserved range", $field),
            actual: $this->translate(new $class(allowPrivateRange: false, allowReservedRange: false), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_is_boolean(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a boolean', $field),
            actual: $this->translate(new Rules\IsBoolean(), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be a boolean if specified', $field),
            actual: $this->translate(new Rules\IsBoolean(orNull: true), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_is_enum(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be FOO, BAR or BAZ', $field),
            actual: $this->translate(new Rules\IsEnum(UnitEnumFixture::class), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be FOO or BAZ', $field),
            actual: $this->translate(new Rules\IsEnum(UnitEnumFixture::class, except: [UnitEnumFixture::BAR]), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be foo, bar or baz', $field),
            actual: $this->translate(new Rules\IsEnum(BackedEnumFixture::class), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be foo or baz', $field),
            actual: $this->translate(new Rules\IsEnum(BackedEnumFixture::class, except: [BackedEnumFixture::BAR]), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_is_float(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a floating point number', $field),
            actual: $this->translate(new Rules\IsFloat(), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be a floating point number if specified', $field),
            actual: $this->translate(new Rules\IsFloat(orNull: true), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_is_integer(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a number', $field),
            actual: $this->translate(new Rules\IsInteger(), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be a number if specified', $field),
            actual: $this->translate(new Rules\IsInteger(orNull: true), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_is_string(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a string', $field),
            actual: $this->translate(new Rules\IsString(), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be a string or be empty', $field),
            actual: $this->translate(new Rules\IsString(orNull: true), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_is_json(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a valid JSON string', $field),
            actual: $this->translate(new Rules\Json(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_length(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be at least 0', $field),
            actual: $this->translate(new Rules\Length(min: 0), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be at most 0', $field),
            actual: $this->translate(new Rules\Length(max: 0), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be between 0 and 2', $field),
            actual: $this->translate(new Rules\Length(min: 0, max: 2), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_lowercase(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a lowercase string', $field),
            actual: $this->translate(new Rules\Lowercase(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_mac_address(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a valid MAC address', $field),
            actual: $this->translate(new Rules\MACAddress(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_multiple_of(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a multiple of 5', $field, expected: 'Number'),
            actual: $this->translate(new Rules\MultipleOf(divisor: 5), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_not_empty(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must not be empty', $field),
            actual: $this->translate(new Rules\NotEmpty(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_not_in(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must not be a', $field),
            actual: $this->translate(new Rules\NotIn(['a']), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must not be a or b', $field),
            actual: $this->translate(new Rules\NotIn(['a', 'b']), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must not be a, b or c', $field),
            actual: $this->translate(new Rules\NotIn(['a', 'b', 'c']), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_not_null(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be specified', $field),
            actual: $this->translate(new Rules\NotNull(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_numeric(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be numeric', $field),
            actual: $this->translate(new Rules\Numeric(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_odd(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be odd', $field, expected: 'Number'),
            actual: $this->translate(new Rules\Odd(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_password(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must contain at least 12 characters', $field, expected: 'Password'),
            actual: $this->translate(new Rules\Password(), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must contain at least 4 characters', $field, expected: 'Password'),
            actual: $this->translate(new Rules\Password(min: 4), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must contain at least 12 characters, one uppercase and one lowercase letter', $field, expected: 'Password'),
            actual: $this->translate(new Rules\Password(mixedCase: true), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must contain at least 12 characters and one number', $field, expected: 'Password'),
            actual: $this->translate(new Rules\Password(numbers: true), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must contain at least 12 characters and one symbol', $field, expected: 'Password'),
            actual: $this->translate(new Rules\Password(symbols: true), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must contain at least 12 characters and one letter', $field, expected: 'Password'),
            actual: $this->translate(new Rules\Password(letters: true), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField(
                '%s must contain at least 12 characters, one uppercase and one lowercase letter, one number, and one symbol',
                $field,
                expected: 'Password',
            ),
            actual: $this->translate(new Rules\Password(mixedCase: true, numbers: true, symbols: true), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must contain at least 12 characters, one uppercase and one lowercase letter, and one number', $field, expected: 'Password'),
            actual: $this->translate(new Rules\Password(mixedCase: true, numbers: true), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must contain at least 12 characters, one uppercase and one lowercase letter, and one symbol', $field, expected: 'Password'),
            actual: $this->translate(new Rules\Password(mixedCase: true, symbols: true), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must contain at least 12 characters, one number, one letter and one symbol', $field, expected: 'Password'),
            actual: $this->translate(new Rules\Password(letters: true, numbers: true, symbols: true), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must contain at least 12 characters, one number and one letter', $field, expected: 'Password'),
            actual: $this->translate(new Rules\Password(letters: true, numbers: true), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must contain at least 12 characters, one letter and one symbol', $field, expected: 'Password'),
            actual: $this->translate(new Rules\Password(letters: true, symbols: true), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must contain at least 12 characters, one number and one symbol', $field, expected: 'Password'),
            actual: $this->translate(new Rules\Password(numbers: true, symbols: true), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must contain at least 12 characters, one number and one letter', $field, expected: 'Password'),
            actual: $this->translate(new Rules\Password(letters: true, numbers: true), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_phone_number(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a phone number', $field),
            actual: $this->translate(new Rules\PhoneNumber(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_regex(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must match the pattern /[A-Z]/', $field),
            actual: $this->translate(new Rules\RegEx('/[A-Z]/'), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_should_be_false(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be false', $field),
            actual: $this->translate(new Rules\ShouldBeFalse(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_should_be_true(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be true', $field),
            actual: $this->translate(new Rules\ShouldBeTrue(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_starts_with(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must start with foo', $field),
            actual: $this->translate(new Rules\StartsWith('foo'), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_time(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a valid time in 24-hour format', $field),
            actual: $this->translate(new Rules\Time(), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be a valid time in 12-hour format', $field),
            actual: $this->translate(new Rules\Time(false), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_timestamp(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a valid timestamp', $field),
            actual: $this->translate(new Rules\Timestamp(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_timezone(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a valid timezone', $field),
            actual: $this->translate(new Rules\Timezone(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_ulid(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a lexicographically sortable identifier', $field),
            actual: $this->translate(new Rules\Ulid(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_uppercase(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be an uppercase string', $field),
            actual: $this->translate(new Rules\Uppercase(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_url(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a valid URL', $field),
            actual: $this->translate(new Rules\Url(), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be a URL using https', $field),
            actual: $this->translate(new Rules\Url(['https']), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be a URL using https or http', $field),
            actual: $this->translate(new Rules\Url(['https', 'http']), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be a URL using ftp, https or http', $field),
            actual: $this->translate(new Rules\Url(['ftp', 'https', 'http']), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    public function test_uuid(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a universally unique identifier', $field),
            actual: $this->translate(new Rules\Uuid(), field: $field),
        );
    }
}
