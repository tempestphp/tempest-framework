<?php

namespace Tempest\Support\Tests\Number;

use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Currency;
use Tempest\Support\Language\Locale;
use Tempest\Support\Number;

final class FunctionsTest extends TestCase
{
    #[RequiresPhpExtension('intl')]
    public function test_format(): void
    {
        $this->assertSame('0', Number\format(0));
        $this->assertSame('0', Number\format(0.0));
        $this->assertSame('0', Number\format(0.00));
        $this->assertSame('1', Number\format(1));
        $this->assertSame('10', Number\format(10));
        $this->assertSame('25', Number\format(25));
        $this->assertSame('100', Number\format(100));
        $this->assertSame('100,000', Number\format(100000));
        $this->assertSame('100,000.00', Number\format(100000, precision: 2));
        $this->assertSame('100,000.12', Number\format(100000.123, precision: 2));
        $this->assertSame('100,000.123', Number\format(100000.1234, maxPrecision: 3));
        $this->assertSame('100,000.124', Number\format(100000.1236, maxPrecision: 3));
        $this->assertSame('123,456,789', Number\format(123456789));

        $this->assertSame('-1', Number\format(-1));
        $this->assertSame('-10', Number\format(-10));
        $this->assertSame('-25', Number\format(-25));

        $this->assertSame('0.2', Number\format(0.2));
        $this->assertSame('0.20', Number\format(0.2, precision: 2));
        $this->assertSame('0.123', Number\format(0.1234, maxPrecision: 3));
        $this->assertSame('1.23', Number\format(1.23));
        $this->assertSame('-1.23', Number\format(-1.23));
        $this->assertSame('123.456', Number\format(123.456));

        $this->assertSame('∞', Number\format(INF));
        $this->assertSame('NaN', Number\format(NAN));
    }

    #[RequiresPhpExtension('intl')]
    public function test_format_with_different_locale(): void
    {
        $this->assertSame('123,456,789', Number\format(123_456_789, locale: Locale::ENGLISH));
        $this->assertSame('123.456.789', Number\format(123_456_789, locale: Locale::GERMAN));
        $this->assertSame('123 456 789', Number\format(123_456_789, locale: Locale::FRENCH));
        $this->assertSame('123 456 789', Number\format(123_456_789, locale: Locale::RUSSIAN));
        $this->assertSame('123 456 789', Number\format(123_456_789, locale: Locale::SWEDISH));
    }

    #[RequiresPhpExtension('intl')]
    public function test_spellout(): void
    {
        $this->assertSame('ten', Number\spell_out(10));
        $this->assertSame('one point two', Number\spell_out(1.2));
    }

    #[RequiresPhpExtension('intl')]
    public function test_spellout_with_locale(): void
    {
        $this->assertSame('trois', Number\spell_out(3, Locale::FRENCH));
    }

    #[RequiresPhpExtension('intl')]
    public function test_spellout_with_threshold(): void
    {
        $this->assertSame('9', Number\spell_out(9, after: 10));
        $this->assertSame('10', Number\spell_out(10, after: 10));
        $this->assertSame('eleven', Number\spell_out(11, after: 10));

        $this->assertSame('nine', Number\spell_out(9, until: 10));
        $this->assertSame('10', Number\spell_out(10, until: 10));
        $this->assertSame('11', Number\spell_out(11, until: 10));

        $this->assertSame('ten thousand', Number\spell_out(10000, until: 50000));
        $this->assertSame('100,000', Number\spell_out(100000, until: 50000));
    }

    #[RequiresPhpExtension('intl')]
    public function test_ordinal(): void
    {
        $this->assertSame('1st', Number\to_ordinal(1));
        $this->assertSame('2nd', Number\to_ordinal(2));
        $this->assertSame('3rd', Number\to_ordinal(3));
    }

    #[RequiresPhpExtension('intl')]
    public function test_spell_ordinal(): void
    {
        $this->assertSame('first', Number\to_spelled_ordinal(1));
        $this->assertSame('second', Number\to_spelled_ordinal(2));
        $this->assertSame('third', Number\to_spelled_ordinal(3));
    }

    #[RequiresPhpExtension('intl')]
    public function test_to_percent(): void
    {
        $this->assertSame('0%', Number\to_percentage(0, precision: 0));
        $this->assertSame('0%', Number\to_percentage(0));
        $this->assertSame('1%', Number\to_percentage(1));
        $this->assertSame('10.00%', Number\to_percentage(10, precision: 2));
        $this->assertSame('100%', Number\to_percentage(100));
        $this->assertSame('100.00%', Number\to_percentage(100, precision: 2));
        $this->assertSame('100.123%', Number\to_percentage(100.1234, maxPrecision: 3));

        $this->assertSame('300%', Number\to_percentage(300));
        $this->assertSame('1,000%', Number\to_percentage(1000));

        $this->assertSame('2%', Number\to_percentage(1.75));
        $this->assertSame('1.75%', Number\to_percentage(1.75, precision: 2));
        $this->assertSame('1.750%', Number\to_percentage(1.75, precision: 3));
        $this->assertSame('0%', Number\to_percentage(0.12345));
        $this->assertSame('0.00%', Number\to_percentage(0, precision: 2));
        $this->assertSame('0.12%', Number\to_percentage(0.12345, precision: 2));
        $this->assertSame('0.1235%', Number\to_percentage(0.12345, precision: 4));
    }

    #[RequiresPhpExtension('intl')]
    public function test_to_currency(): void
    {
        $this->assertSame('$0.00', Number\currency(0, Currency::USD));
        $this->assertSame('$1.00', Number\currency(1, Currency::USD));
        $this->assertSame('$10.00', Number\currency(10, Currency::USD));

        $this->assertSame('€0.00', Number\currency(0, Currency::EUR));
        $this->assertSame('€1.00', Number\currency(1, Currency::EUR));
        $this->assertSame('€10.00', Number\currency(10, Currency::EUR));

        $this->assertSame('-$5.00', Number\currency(-5, Currency::USD));
        $this->assertSame('$5.00', Number\currency(5.00, Currency::USD));
        $this->assertSame('$5.32', Number\currency(5.325, Currency::USD));

        $this->assertSame('$0', Number\currency(0, Currency::USD, precision: 0));
        $this->assertSame('$5', Number\currency(5.00, Currency::USD, precision: 0));
        $this->assertSame('$10', Number\currency(10.252, Currency::USD, precision: 0));
    }

    #[RequiresPhpExtension('intl')]
    public function test_to_currency_with_different_locale(): void
    {
        $this->assertSame('1,00 €', Number\currency(1, Currency::EUR, Locale::GERMAN));
        $this->assertSame('1,00 $', Number\currency(1, Currency::USD, Locale::GERMAN));
        $this->assertSame('1,00 £', Number\currency(1, Currency::GBP, Locale::GERMAN));

        $this->assertSame('123.456.789,12 $', Number\currency(123456789.12345, Currency::USD, Locale::GERMAN));
        $this->assertSame('123.456.789,12 €', Number\currency(123456789.12345, Currency::EUR, Locale::GERMAN));
        $this->assertSame('1 234,56 $US', Number\currency(1234.56, Currency::USD, Locale::FRENCH));
    }

    #[RequiresPhpExtension('intl')]
    public function test_bytes_to_human(): void
    {
        $this->assertSame('0 B', Number\to_file_size(0));
        $this->assertSame('0.00 B', Number\to_file_size(0, precision: 2));
        $this->assertSame('1 B', Number\to_file_size(1));
        $this->assertSame('1 KB', Number\to_file_size(1000));
        $this->assertSame('2 KB', Number\to_file_size(2000));
        $this->assertSame('2.00 KB', Number\to_file_size(2000, precision: 2));
        $this->assertSame('1.23 KB', Number\to_file_size(1234, precision: 2));
        $this->assertSame('1.234 KB', Number\to_file_size(1234, maxPrecision: 3));
        $this->assertSame('1.234 KB', Number\to_file_size(1234, 3));
        $this->assertSame('5 GB', Number\to_file_size(1000 * 1000 * 1000 * 5));
        $this->assertSame('10 TB', Number\to_file_size((1000 ** 4) * 10));
        $this->assertSame('10 PB', Number\to_file_size((1000 ** 5) * 10));
        $this->assertSame('1 ZB', Number\to_file_size(1000 ** 7));
        $this->assertSame('1 YB', Number\to_file_size(1000 ** 8));
        $this->assertSame('1 RB', Number\to_file_size(1000 ** 9));
        $this->assertSame('1 QB', Number\to_file_size(1000 ** 10));
        $this->assertSame('1,000 QB', Number\to_file_size(1000 ** 11));

        $this->assertSame('0 B', Number\to_file_size(0, useBinaryPrefix: true));
        $this->assertSame('0.00 B', Number\to_file_size(0, precision: 2, useBinaryPrefix: true));
        $this->assertSame('1 B', Number\to_file_size(1, useBinaryPrefix: true));
        $this->assertSame('1 KiB', Number\to_file_size(1024, useBinaryPrefix: true));
        $this->assertSame('2 KiB', Number\to_file_size(2048, useBinaryPrefix: true));
        $this->assertSame('2.00 KiB', Number\to_file_size(2048, precision: 2, useBinaryPrefix: true));
        $this->assertSame('1.23 KiB', Number\to_file_size(1264, precision: 2, useBinaryPrefix: true));
        $this->assertSame('1.234 KiB', Number\to_file_size(1264.12345, maxPrecision: 3, useBinaryPrefix: true));
        $this->assertSame('1.234 KiB', Number\to_file_size(1264, 3, useBinaryPrefix: true));
        $this->assertSame('5 GiB', Number\to_file_size(1024 * 1024 * 1024 * 5, useBinaryPrefix: true));
        $this->assertSame('10 TiB', Number\to_file_size((1024 ** 4) * 10, useBinaryPrefix: true));
        $this->assertSame('10 PiB', Number\to_file_size((1024 ** 5) * 10, useBinaryPrefix: true));
        $this->assertSame('1 ZiB', Number\to_file_size(1024 ** 7, useBinaryPrefix: true));
        $this->assertSame('1 YiB', Number\to_file_size(1024 ** 8, useBinaryPrefix: true));
        $this->assertSame('1 RiB', Number\to_file_size(1024 ** 9, useBinaryPrefix: true));
        $this->assertSame('1 QiB', Number\to_file_size(1024 ** 10, useBinaryPrefix: true));
        $this->assertSame('1,024 QiB', Number\to_file_size(1024 ** 11, useBinaryPrefix: true));
    }

    #[RequiresPhpExtension('intl')]
    public function test_summarize(): void
    {
        $this->assertSame('1', Number\to_human_readable(1));
        $this->assertSame('1.00', Number\to_human_readable(1, precision: 2));
        $this->assertSame('10', Number\to_human_readable(10));
        $this->assertSame('100', Number\to_human_readable(100));
        $this->assertSame('1K', Number\to_human_readable(1000));
        $this->assertSame('1.00K', Number\to_human_readable(1000, precision: 2));
        $this->assertSame('1K', Number\to_human_readable(1000, maxPrecision: 2));
        $this->assertSame('1K', Number\to_human_readable(1230));
        $this->assertSame('1.2K', Number\to_human_readable(1230, maxPrecision: 1));
        $this->assertSame('1M', Number\to_human_readable(1000000));
        $this->assertSame('1B', Number\to_human_readable(1000000000));
        $this->assertSame('1T', Number\to_human_readable(1000000000000));
        $this->assertSame('1Q', Number\to_human_readable(1000000000000000));
        $this->assertSame('1KQ', Number\to_human_readable(1000000000000000000));

        $this->assertSame('123', Number\to_human_readable(123));
        $this->assertSame('1K', Number\to_human_readable(1234));
        $this->assertSame('1.23K', Number\to_human_readable(1234, precision: 2));
        $this->assertSame('12K', Number\to_human_readable(12345));
        $this->assertSame('1M', Number\to_human_readable(1234567));
        $this->assertSame('1B', Number\to_human_readable(1234567890));
        $this->assertSame('1T', Number\to_human_readable(1234567890123));
        $this->assertSame('1.23T', Number\to_human_readable(1234567890123, precision: 2));
        $this->assertSame('1Q', Number\to_human_readable(1234567890123456));
        $this->assertSame('1.23KQ', Number\to_human_readable(1234567890123456789, precision: 2));
        $this->assertSame('490K', Number\to_human_readable(489939));
        $this->assertSame('489.9390K', Number\to_human_readable(489939, precision: 4));
        $this->assertSame('500.00000M', Number\to_human_readable(500000000, precision: 5));

        $this->assertSame('1MQ', Number\to_human_readable(1000000000000000000000));
        $this->assertSame('1BQ', Number\to_human_readable(1000000000000000000000000));
        $this->assertSame('1TQ', Number\to_human_readable(1000000000000000000000000000));
        $this->assertSame('1QQ', Number\to_human_readable(1000000000000000000000000000000));
        $this->assertSame('1KQQ', Number\to_human_readable(1000000000000000000000000000000000));

        $this->assertSame('0', Number\to_human_readable(0));
        $this->assertSame('0', Number\to_human_readable(0.0));
        $this->assertSame('0.00', Number\to_human_readable(0, 2));
        $this->assertSame('0.00', Number\to_human_readable(0.0, 2));
        $this->assertSame('-1', Number\to_human_readable(-1));
        $this->assertSame('-1.00', Number\to_human_readable(-1, precision: 2));
        $this->assertSame('-10', Number\to_human_readable(-10));
        $this->assertSame('-100', Number\to_human_readable(-100));
        $this->assertSame('-1K', Number\to_human_readable(-1000));
        $this->assertSame('-1.23K', Number\to_human_readable(-1234, precision: 2));
        $this->assertSame('-1.2K', Number\to_human_readable(-1234, maxPrecision: 1));
        $this->assertSame('-1M', Number\to_human_readable(-1000000));
        $this->assertSame('-1B', Number\to_human_readable(-1000000000));
        $this->assertSame('-1T', Number\to_human_readable(-1000000000000));
        $this->assertSame('-1.1T', Number\to_human_readable(-1100000000000, maxPrecision: 1));
        $this->assertSame('-1Q', Number\to_human_readable(-1000000000000000));
        $this->assertSame('-1KQ', Number\to_human_readable(-1000000000000000000));
    }
}
