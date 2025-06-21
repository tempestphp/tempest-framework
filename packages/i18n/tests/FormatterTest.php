<?php

namespace Tempest\Internationalization\Tests;

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Internationalization\InternationalizationConfig;
use Tempest\Internationalization\MessageFormat\Formatter\FormattedValue;
use Tempest\Internationalization\MessageFormat\Formatter\MessageFormatFunction;
use Tempest\Internationalization\MessageFormat\Formatter\MessageFormatter;
use Tempest\Internationalization\MessageFormat\Functions\DateTimeFunction;
use Tempest\Internationalization\MessageFormat\Functions\NumberFunction;
use Tempest\Internationalization\MessageFormat\Functions\StringFunction;
use Tempest\Support\Currency;
use Tempest\Support\Language\Locale;

final class FormatterTest extends TestCase
{
    public function test_format_markup(): void
    {
        $formatter = new MessageFormatter();
        $value = $formatter->format(<<<'TXT'
        This is {#bold}bold{/bold}.
        TXT);

        // TODO: offer custom markup

        $this->assertSame('This is <bold>bold</bold>.', $value);
    }

    public function test_placeholder_variable(): void
    {
        $formatter = new MessageFormatter();
        $value = $formatter->format(<<<'TXT'
        Hello, {$name}!
        TXT, name: 'Jon');

        $this->assertSame('Hello, Jon!', $value);
    }

    public function test_format_datetime_function(): void
    {
        $formatter = new MessageFormatter([new DateTimeFunction()]);

        $value = $formatter->format(<<<'TXT'
        Today is {$today :datetime}.
        TXT, today: '2024-01-01');

        $this->assertSame("Today is Jan 1, 2024, 12:00:00\u{202F}AM.", $value);
    }

    public function test_format_datetime_function_and_parameters(): void
    {
        $formatter = new MessageFormatter([new DateTimeFunction()]);

        $value = $formatter->format(<<<'TXT'
        Today is {$today :datetime pattern=|yyyy/MM/dd|}.
        TXT, today: '2024-01-01');

        $this->assertSame('Today is 2024/01/01.', $value);
    }

    public function test_format_number_function(): void
    {
        $formatter = new MessageFormatter([new NumberFunction()]);

        $value = $formatter->format(<<<'TXT'
        The total was {31 :number style=percent}.
        TXT);

        $this->assertSame('The total was 31%.', $value);
    }

    #[TestWith([0, "pas d'avion"])]
    #[TestWith([1, 'un avion'])]
    #[TestWith([5, '5 avions'])]
    public function test_match_number(int $count, string $expected): void
    {
        $formatter = new MessageFormatter([new NumberFunction()]);

        $value = $formatter->format(<<<'TXT'
        .input {$aircraft :number}
        .match $aircraft
            0 {{pas d'avion}}
            1 {{un avion}}
            * {{{$aircraft} avions}}
        TXT, aircraft: $count);

        $this->assertSame($expected, $value);
    }

    public function test_unquoted_text(): void
    {
        $formatter = new MessageFormatter();
        $value = $formatter->format(<<<'TXT'
        Hello, {world}!
        TXT);

        $this->assertSame('Hello, world!', $value);
    }

    public function test_quoted_text(): void
    {
        $formatter = new MessageFormatter();
        $value = $formatter->format(<<<'TXT'
        My name is {|John Doe|}.
        TXT);

        $this->assertSame('My name is John Doe.', $value);
    }

    public function test_matchers(): void
    {
        $formatter = new MessageFormatter();
        $value = $formatter->format(<<<'TXT'
        .input {$count :number}
        .match $count
        one {{You have {$count} notification.}}
        *   {{You have {$count} notifications.}}
        TXT, count: 1);

        $this->assertSame('You have 1 notification.', $value);
    }

    public function test_whitespace(): void
    {
        $formatter = new MessageFormatter();
        $value = $formatter->format(<<<'TXT'
        .input {$num :number}
        {{   This is the {$num} pattern   }}
        TXT, num: 5);

        $this->assertSame('   This is the 5 pattern   ', $value);
    }

    public function test_escape(): void
    {
        $formatter = new MessageFormatter();
        $value = $formatter->format(<<<'TXT'
        Backslash: \\, left curly brace \{, right curly brace \}
        TXT);

        $this->assertSame('Backslash: \, left curly brace {, right curly brace }', $value);
    }

    public function test_matchers_escape(): void
    {
        $formatter = new MessageFormatter();
        $value = $formatter->format(<<<'TXT'
        .input {$char :string}
        .match $char
        | |  {{You entered a space character.}}
        |\|| {{You entered a pipe character.}}
        *    {{You entered something else.}}
        TXT, char: '|');

        $this->assertSame('You entered a pipe character.', $value);
    }

    public function test_matchers_number_exact_match(): void
    {
        $formatter = new MessageFormatter([new NumberFunction()]);

        $value = $formatter->format(<<<'TXT'
        .input {$numDays :number select=exact}
        .match $numDays
        1  {{{$numDays} one}}
        2  {{{$numDays} two}}
        3 {{{$numDays} three}}
        TXT, numDays: 2);

        $this->assertSame('2 two', $value);
    }

    #[TestWith([1, '1 den'])]
    #[TestWith([2, '2 dny'])]
    #[TestWith([1.5, '1.5 dne'])]
    #[TestWith([5, '5 dní'])]
    public function test_matchers_czech(int|float $days, string $expected): void
    {
        locale_set_default(Locale::CZECH->value);

        $formatter = new MessageFormatter([new NumberFunction()]);

        $value = $formatter->format(<<<'TXT'
        .input {$days :number}
        .match $days
        one  {{{$days} den}}
        few  {{{$days} dny}}
        many {{{$days} dne}}
        *    {{{$days} dní}}
        TXT, days: $days);

        $this->assertSame($expected, $value);
    }

    public function test_string_function(): void
    {
        $formatter = new MessageFormatter([new NumberFunction()]);

        $value = $formatter->format(<<<'TXT'
        .input {$operand :string}
        .match $operand
        1    {{Number 1}}
        one  {{String "one"}}
        *    {{Something else}}
        TXT, operand: 1);

        $this->assertSame('Number 1', $value);
    }

    #[TestWith(['value', 'value'])]
    #[TestWith([1, '1'])]
    #[TestWith([1.1, '1.1'])]
    #[TestWith([['name' => 'Jon'], ''])]
    public function test_string_formatting(mixed $input, string $expected): void
    {
        $formatter = new MessageFormatter([new StringFunction()]);

        $value = $formatter->format(<<<'TXT'
        {$value :string}
        TXT, value: $input);

        $this->assertSame($expected, $value);
    }

    #[TestWith(['value', 'VALUE', 'upper'])]
    #[TestWith(['VALUE', 'value', 'lower'])]
    #[TestWith(['my value', 'my_value', 'snake'])]
    #[TestWith(['my value', 'my-value', 'kebab'])]
    #[TestWith(['my value', 'My value', 'sentence'])]
    #[TestWith(['my value', 'myValue', 'camel'])]
    #[TestWith(['my value', 'My Value', 'title'])]
    public function test_string_formatting_options(mixed $input, string $expected, string $style): void
    {
        $formatter = new MessageFormatter([new StringFunction()]);

        $value = $formatter->format(<<<TXT
        {\$value :string style=$style}
        TXT, value: $input);

        $this->assertSame($expected, $value);
    }

    public function test_number_currency(): void
    {
        $formatter = new MessageFormatter([new NumberFunction()]);

        $value = $formatter->format(<<<'TXT'
        You have {42 :number style=currency currency=$currency}.
        TXT, currency: Currency::USD);

        $this->assertSame('You have $42.00.', $value);
    }

    public function test_shadowing(): void
    {
        $formatter = new MessageFormatter([new NumberFunction()]);

        $value = $formatter->format(<<<'TXT'
        .local $count = {42}
        {{The count is: {$count}}}
        TXT, count: 32);

        $this->assertSame('The count is: 42', $value);
    }

    #[TestWith([0, 'No items.'])]
    #[TestWith([1, '1 item.'])]
    #[TestWith([5, '5 items.'])]
    public function test_pluralization(int $count, string $expected): void
    {
        $formatter = new MessageFormatter([
            new NumberFunction(),
        ]);

        $value = $formatter->format(<<<'TXT'
        .input {$count :number}
        .match $count
        0   {{No items.}}
        one {{1 item.}}
        *   {{{$count} items.}}
        TXT, count: $count);

        $this->assertSame($expected, $value);
    }

    public function test_multiple_selectors(): void
    {
        $formatter = new MessageFormatter([
            new NumberFunction(),
            new DateTimeFunction(),
        ]);

        $value = $formatter->format(<<<'TXT'
        .input {$hostGender :string}
        .input {$guestCount :number}
        .match $hostGender $guestCount
        female 0 {{{$hostName} does not give a party.}}
        female 1 {{{$hostName} invites {$guestName} to her party.}}
        female 2 {{{$hostName} invites {$guestName} and one other person to her party.}}
        female * {{{$hostName} invites {$guestCount} people, including {$guestName}, to her party.}}
        male   0 {{{$hostName} does not give a party.}}
        male   1 {{{$hostName} invites {$guestName} to his party.}}
        male   2 {{{$hostName} invites {$guestName} and one other person to his party.}}
        male   * {{{$hostName} invites {$guestCount} people, including {$guestName}, to his party.}}
        *      0 {{{$hostName} does not give a party.}}
        *      1 {{{$hostName} invites {$guestName} to their party.}}
        *      2 {{{$hostName} invites {$guestName} and one other person to their party.}}
        *      * {{{$hostName} invites {$guestCount} people, including {$guestName}, to their party.}}
        TXT, hostGender: 'female', hostName: 'Alice', guestCount: 2, guestName: 'Bob');

        $this->assertSame('Alice invites Bob and one other person to her party.', $value);
    }

    public function test_custom_function(): void
    {
        $formatter = new MessageFormatter([
            new class implements MessageFormatFunction {
                public string $name = 'uppercase';

                public function evaluate(mixed $value, array $parameters): FormattedValue
                {
                    return new FormattedValue($value, mb_strtoupper($value));
                }
            },
        ]);

        $value = $formatter->format(<<<'TXT'
        Check out {MessageFormat :uppercase}.
        TXT);

        $this->assertSame('Check out MESSAGEFORMAT.', $value);
    }
}
