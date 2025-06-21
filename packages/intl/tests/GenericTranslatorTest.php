<?php

namespace Tempest\Intl\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\Intl\Catalog\Catalog;
use Tempest\Intl\Catalog\GenericCatalog;
use Tempest\Intl\GenericTranslator;
use Tempest\Intl\IntlConfig;
use Tempest\Intl\Locale;
use Tempest\Intl\MessageFormat\Formatter\MessageFormatter;
use Tempest\Intl\MessageFormat\Functions\DateTimeFunction;
use Tempest\Intl\MessageFormat\Functions\NumberFunction;
use Tempest\Intl\MessageFormat\Functions\StringFunction;
use Tempest\Intl\Translator;

final class GenericTranslatorTest extends TestCase
{
    private Catalog $catalog;
    private Translator $translator;
    private IntlConfig $config;

    protected function setUp(): void
    {
        $this->catalog = new GenericCatalog();
        $this->catalog->add(Locale::FRENCH, 'hello', 'Bonjour!');
        $this->catalog->add(Locale::ENGLISH, 'hello', 'Hello!');

        $this->config = new IntlConfig(
            currentLocale: Locale::FRENCH,
            fallbackLocale: Locale::ENGLISH,
        );

        $this->translator = new GenericTranslator(
            config: $this->config,
            catalog: $this->catalog,
            formatter: new MessageFormatter([
                new StringFunction(),
                new NumberFunction(),
                new DateTimeFunction(),
            ]),
        );
    }

    public function test_translate(): void
    {
        // existing
        $this->assertSame('Bonjour!', $this->translator->translate('hello'));

        // add to catalog
        $this->catalog->add(Locale::ENGLISH, 'goodbye', 'Goodbye!');
        $this->assertSame('Goodbye!', $this->translator->translate('goodbye'));
    }

    public function test_fallback(): void
    {
        $this->config->currentLocale = Locale::FRENCH;
        $this->config->fallbackLocale = Locale::ENGLISH;

        $this->catalog->add(Locale::ENGLISH, 'aircraft_count', '{$count :number} aircraft');
        $this->assertSame('2 aircraft', $this->translator->translate('aircraft_count', count: 2));
    }

    public function test_complex_message(): void
    {
        $this->config->currentLocale = Locale::FRENCH;

        $this->catalog->add(Locale::FRENCH, 'aircraft_count', <<<'MF2'
        .input {$aircraft :number}
        .match $aircraft
            0 {{pas d'avion}}
            1 {{un avion}}
            * {{{$aircraft} avions}}
        MF2);

        $this->assertSame("pas d'avion", $this->translator->translate('aircraft_count', aircraft: 0));
        $this->assertSame('un avion', $this->translator->translate('aircraft_count', aircraft: 1));
        $this->assertSame('2 avions', $this->translator->translate('aircraft_count', aircraft: 2));
    }

    public function test_translate_missing(): void
    {
        $this->assertSame('missing_key', $this->translator->translate('missing_key'));
        $this->assertSame('missing.key', $this->translator->translate('missing.key'));
    }

    public function test_translate_variables(): void
    {
        $this->catalog->add(Locale::ENGLISH, 'goodbye_user', 'Goodbye, {$user :string}!');

        $this->assertSame('Goodbye, Jon Doe!', $this->translator->translate('goodbye_user', user: 'Jon Doe'));
    }

    public function test_change_locale(): void
    {
        $this->config->currentLocale = Locale::ENGLISH;
        $this->assertSame('Hello!', $this->translator->translate('hello'));

        $this->config->currentLocale = Locale::FRENCH;
        $this->assertSame('Bonjour!', $this->translator->translate('hello'));
    }

    public function test_translate_for_locale(): void
    {
        $this->config->currentLocale = Locale::ENGLISH;

        $this->assertSame('Bonjour!', $this->translator->translateForLocale(Locale::FRENCH, 'hello'));
        $this->assertSame('Hello!', $this->translator->translateForLocale(Locale::ENGLISH, 'hello'));
        $this->assertSame('missing_key', $this->translator->translateForLocale(Locale::FRENCH, 'missing_key'));
    }
}
