<?php

namespace Tests\Tempest\Integration\Validator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tempest\DateTime\FormatPattern;
use Tempest\Validation\Rule;
use Tempest\Validation\Rules;
use Tempest\Validation\Rules\DoesNotEndWith;
use Tempest\Validation\Rules\DoesNotStartWith;
use Tempest\Validation\Rules\EndsWith;
use Tempest\Validation\Rules\Exists;
use Tempest\Validation\Rules\HasCount;
use Tempest\Validation\Rules\HasDateTimeFormat;
use Tempest\Validation\Rules\HasLength;
use Tempest\Validation\Rules\IsAfterDate;
use Tempest\Validation\Rules\IsAlpha;
use Tempest\Validation\Rules\IsAlphaNumeric;
use Tempest\Validation\Rules\IsArrayList;
use Tempest\Validation\Rules\IsBeforeDate;
use Tempest\Validation\Rules\IsBetween;
use Tempest\Validation\Rules\IsBetweenDates;
use Tempest\Validation\Rules\IsBoolean;
use Tempest\Validation\Rules\IsDivisibleBy;
use Tempest\Validation\Rules\IsEmail;
use Tempest\Validation\Rules\IsEnum;
use Tempest\Validation\Rules\IsEvenNumber;
use Tempest\Validation\Rules\IsFalsy;
use Tempest\Validation\Rules\IsFloat;
use Tempest\Validation\Rules\IsHexColor;
use Tempest\Validation\Rules\IsIn;
use Tempest\Validation\Rules\IsInteger;
use Tempest\Validation\Rules\IsIP;
use Tempest\Validation\Rules\IsIPv4;
use Tempest\Validation\Rules\IsIPv6;
use Tempest\Validation\Rules\IsJsonString;
use Tempest\Validation\Rules\IsLowercase;
use Tempest\Validation\Rules\IsMacAddress;
use Tempest\Validation\Rules\IsMultipleOf;
use Tempest\Validation\Rules\IsNotEmptyString;
use Tempest\Validation\Rules\IsNotIn;
use Tempest\Validation\Rules\IsNotNull;
use Tempest\Validation\Rules\IsNumeric;
use Tempest\Validation\Rules\IsOddNumber;
use Tempest\Validation\Rules\IsPassword;
use Tempest\Validation\Rules\IsPhoneNumber;
use Tempest\Validation\Rules\IsString;
use Tempest\Validation\Rules\IsTime;
use Tempest\Validation\Rules\IsTimezone;
use Tempest\Validation\Rules\IsTruthy;
use Tempest\Validation\Rules\IsUlid;
use Tempest\Validation\Rules\IsUnixTimestamp;
use Tempest\Validation\Rules\IsUppercase;
use Tempest\Validation\Rules\IsUrl;
use Tempest\Validation\Rules\IsUuid;
use Tempest\Validation\Rules\MatchesRegEx;
use Tempest\Validation\Rules\StartsWith;
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
    #[Test]
    public function after_date(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be after or equal to January 1, 2024', $field, expected: 'Date'),
            actual: $this->translate(new IsAfterDate(date: '2024-01-01', inclusive: true), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be after January 1, 2024', $field, expected: 'Date'),
            actual: $this->translate(new IsAfterDate(date: '2024-01-01', inclusive: false), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Creation date'])]
    #[Test]
    public function before_date(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a date before or equal to January 1, 2024', $field, expected: 'Date'),
            actual: $this->translate(new IsBeforeDate(date: '2024-01-01', inclusive: true), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be a date before January 1, 2024', $field, expected: 'Date'),
            actual: $this->translate(new IsBeforeDate(date: '2024-01-01', inclusive: false), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Creation date'])]
    #[Test]
    public function between_dates(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a date between January 1, 2024 and January 1, 2025, included', $field, expected: 'Date'),
            actual: $this->translate(new IsBetweenDates(first: '2024-01-01', second: '2025-01-01', inclusive: true), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be a date between January 1, 2024 and January 1, 2025', $field, expected: 'Date'),
            actual: $this->translate(new IsBetweenDates(first: '2024-01-01', second: '2025-01-01'), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function alpha(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must contain only alphabetic characters', $field),
            actual: $this->translate(new IsAlpha(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function alpha_numeric(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must contain only alphanumeric characters', $field),
            actual: $this->translate(new IsAlphaNumeric(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function array_list(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a list', $field),
            actual: $this->translate(new IsArrayList(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function between(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be between 0 and 2', $field, expected: 'Number'),
            actual: $this->translate(new IsBetween(min: 0, max: 2), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be between 2 and 4', $field, expected: 'Number'),
            actual: $this->translate(new IsBetween(min: 2, max: 4), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Array'])]
    #[Test]
    public function translates_count_rule(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must have at least 3 items', $field),
            actual: $this->translate(new HasCount(min: 3), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must have at least 0 items', $field),
            actual: $this->translate(new HasCount(min: 0), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must have at most 10 items', $field),
            actual: $this->translate(new HasCount(max: 10), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must have between 3 and 10 items', $field),
            actual: $this->translate(new HasCount(min: 3, max: 10), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must have between 0 and 2 items', $field),
            actual: $this->translate(new HasCount(min: 0, max: 2), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function date_time_format(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must use the format yyyy-MM-dd', $field),
            actual: $this->translate(new HasDateTimeFormat('yyyy-MM-dd'), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must use the format MM/dd/yyyy', $field),
            actual: $this->translate(new HasDateTimeFormat(FormatPattern::AMERICAN), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function divisible_by(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be divisible by 1', $field, expected: 'Number'),
            actual: $this->translate(new IsDivisibleBy(divisor: 1), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function does_not_end_with(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must not end with "foo"', $field),
            actual: $this->translate(new DoesNotEndWith(needle: 'foo'), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function email(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a valid email address', $field, expected: 'Email'),
            actual: $this->translate(new IsEmail(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function does_not_start_with(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must not start with "foo"', $field),
            actual: $this->translate(new DoesNotStartWith(needle: 'foo'), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function ends_with(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must end with "foo"', $field),
            actual: $this->translate(new EndsWith(needle: 'foo'), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function exists(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s could not be found', $field),
            actual: $this->translate(new Exists(table: 'non-existing-table', column: 'non-existing-column'), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function even(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be even', $field, expected: 'Number'),
            actual: $this->translate(new IsEvenNumber(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function hex_color(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a hexadecimal color', $field),
            actual: $this->translate(new IsHexColor(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function in(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a', $field),
            actual: $this->translate(new IsIn(['a']), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be a or b', $field),
            actual: $this->translate(new IsIn(['a', 'b']), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be a, b or c', $field),
            actual: $this->translate(new IsIn(['a', 'b', 'c']), field: $field),
        );
    }

    #[TestWith(['IP', null])]
    #[TestWith(['IP', 'Input'])]
    #[TestWith(['IPv4', null])]
    #[TestWith(['IPv4', 'Input'])]
    #[TestWith(['IPv6', null])]
    #[TestWith(['IPv6', 'Input'])]
    #[Test]
    public function ip(string $ip, ?string $field = null): void
    {
        $class = match ($ip) {
            'IPv4' => IsIPv4::class,
            'IPv6' => IsIPv6::class,
            default => IsIP::class,
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
    #[Test]
    public function is_boolean(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a boolean', $field),
            actual: $this->translate(new IsBoolean(), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be a boolean if specified', $field),
            actual: $this->translate(new IsBoolean(orNull: true), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function is_enum(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be FOO, BAR or BAZ', $field),
            actual: $this->translate(new IsEnum(UnitEnumFixture::class), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be FOO or BAZ', $field),
            actual: $this->translate(new IsEnum(UnitEnumFixture::class, except: [UnitEnumFixture::BAR]), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be foo, bar or baz', $field),
            actual: $this->translate(new IsEnum(BackedEnumFixture::class), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be foo or baz', $field),
            actual: $this->translate(new IsEnum(BackedEnumFixture::class, except: [BackedEnumFixture::BAR]), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function is_float(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a floating point number', $field),
            actual: $this->translate(new IsFloat(), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be a floating point number if specified', $field),
            actual: $this->translate(new IsFloat(orNull: true), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function is_integer(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a number', $field),
            actual: $this->translate(new IsInteger(), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be a number if specified', $field),
            actual: $this->translate(new IsInteger(orNull: true), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function is_string(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a string', $field),
            actual: $this->translate(new IsString(), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be a string or be empty', $field),
            actual: $this->translate(new IsString(orNull: true), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function is_json(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a valid JSON string', $field),
            actual: $this->translate(new IsJsonString(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function length(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be at least 0', $field),
            actual: $this->translate(new HasLength(min: 0), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be at most 0', $field),
            actual: $this->translate(new HasLength(max: 0), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be between 0 and 2', $field),
            actual: $this->translate(new HasLength(min: 0, max: 2), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function lowercase(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a lowercase string', $field),
            actual: $this->translate(new IsLowercase(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function mac_address(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a valid MAC address', $field),
            actual: $this->translate(new IsMacAddress(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function multiple_of(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a multiple of 5', $field, expected: 'Number'),
            actual: $this->translate(new IsMultipleOf(divisor: 5), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function not_empty(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must not be empty', $field),
            actual: $this->translate(new IsNotEmptyString(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function not_in(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must not be a', $field),
            actual: $this->translate(new IsNotIn(['a']), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must not be a or b', $field),
            actual: $this->translate(new IsNotIn(['a', 'b']), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must not be a, b or c', $field),
            actual: $this->translate(new IsNotIn(['a', 'b', 'c']), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function not_null(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be specified', $field),
            actual: $this->translate(new IsNotNull(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function numeric(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be numeric', $field),
            actual: $this->translate(new IsNumeric(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function odd(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be odd', $field, expected: 'Number'),
            actual: $this->translate(new IsOddNumber(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function password(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must contain at least 12 characters', $field, expected: 'Password'),
            actual: $this->translate(new IsPassword(), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must contain at least 4 characters', $field, expected: 'Password'),
            actual: $this->translate(new IsPassword(min: 4), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must contain at least 12 characters, one uppercase and one lowercase letter', $field, expected: 'Password'),
            actual: $this->translate(new IsPassword(mixedCase: true), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must contain at least 12 characters and one number', $field, expected: 'Password'),
            actual: $this->translate(new IsPassword(numbers: true), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must contain at least 12 characters and one symbol', $field, expected: 'Password'),
            actual: $this->translate(new IsPassword(symbols: true), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must contain at least 12 characters and one letter', $field, expected: 'Password'),
            actual: $this->translate(new IsPassword(letters: true), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField(
                '%s must contain at least 12 characters, one uppercase and one lowercase letter, one number, and one symbol',
                $field,
                expected: 'Password',
            ),
            actual: $this->translate(new IsPassword(mixedCase: true, numbers: true, symbols: true), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must contain at least 12 characters, one uppercase and one lowercase letter, and one number', $field, expected: 'Password'),
            actual: $this->translate(new IsPassword(mixedCase: true, numbers: true), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must contain at least 12 characters, one uppercase and one lowercase letter, and one symbol', $field, expected: 'Password'),
            actual: $this->translate(new IsPassword(mixedCase: true, symbols: true), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must contain at least 12 characters, one number, one letter and one symbol', $field, expected: 'Password'),
            actual: $this->translate(new IsPassword(numbers: true, letters: true, symbols: true), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must contain at least 12 characters, one number and one letter', $field, expected: 'Password'),
            actual: $this->translate(new IsPassword(numbers: true, letters: true), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must contain at least 12 characters, one letter and one symbol', $field, expected: 'Password'),
            actual: $this->translate(new IsPassword(letters: true, symbols: true), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must contain at least 12 characters, one number and one symbol', $field, expected: 'Password'),
            actual: $this->translate(new IsPassword(numbers: true, symbols: true), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must contain at least 12 characters, one number and one letter', $field, expected: 'Password'),
            actual: $this->translate(new IsPassword(numbers: true, letters: true), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function phone_number(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a phone number', $field),
            actual: $this->translate(new IsPhoneNumber(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function regex(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must match the pattern /[A-Z]/', $field),
            actual: $this->translate(new MatchesRegEx('/[A-Z]/'), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function should_be_false(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be false', $field),
            actual: $this->translate(new IsFalsy(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function should_be_true(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be true', $field),
            actual: $this->translate(new IsTruthy(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function starts_with(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must start with foo', $field),
            actual: $this->translate(new StartsWith('foo'), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function time(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a valid time in 24-hour format', $field),
            actual: $this->translate(new IsTime(), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be a valid time in 12-hour format', $field),
            actual: $this->translate(new IsTime(false), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function timestamp(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a valid timestamp', $field),
            actual: $this->translate(new IsUnixTimestamp(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function timezone(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a valid timezone', $field),
            actual: $this->translate(new IsTimezone(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function ulid(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a lexicographically sortable identifier', $field),
            actual: $this->translate(new IsUlid(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function uppercase(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be an uppercase string', $field),
            actual: $this->translate(new IsUppercase(), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function url(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a valid URL', $field),
            actual: $this->translate(new IsUrl(), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be a URL using https', $field),
            actual: $this->translate(new IsUrl(['https']), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be a URL using https or http', $field),
            actual: $this->translate(new IsUrl(['https', 'http']), field: $field),
        );

        $this->assertSame(
            expected: $this->formatWithField('%s must be a URL using ftp, https or http', $field),
            actual: $this->translate(new IsUrl(['ftp', 'https', 'http']), field: $field),
        );
    }

    #[TestWith([null])]
    #[TestWith(['Input'])]
    #[Test]
    public function uuid(?string $field = null): void
    {
        $this->assertSame(
            expected: $this->formatWithField('%s must be a universally unique identifier', $field),
            actual: $this->translate(new IsUuid(), field: $field),
        );
    }
}
