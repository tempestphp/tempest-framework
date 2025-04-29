<?php

declare(strict_types=1);

namespace Tempest\Support\Tests\Language;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Language\Locale;

use function locale_get_default;
use function locale_set_default;
use function Tempest\Support\Str\replace_every;

final class LocaleTest extends TestCase
{
    private ?string $defaultLocale = null;

    #[\Override]
    protected function setUp(): void
    {
        $this->defaultLocale = locale_get_default();
    }

    #[\Override]
    protected function tearDown(): void
    {
        if (null !== $this->defaultLocale) {
            locale_set_default($this->defaultLocale);
        }
    }

    public function test_default(): void
    {
        foreach (Locale::cases() as $locale) {
            locale_set_default($locale->value);

            $this->assertSame($locale, Locale::default());
        }
    }

    public function test_default_ignores_charset(): void
    {
        locale_set_default('sr_RS.UTF-8');
        $this->assertSame(Locale::SERBIAN_SERBIA, Locale::default());

        locale_set_default('sr_Cyrl.UTF-8');
        $this->assertSame(Locale::SERBIAN_CYRILLIC, Locale::default());
        locale_set_default('sr_Cyrl_RS.UTF-8');
        $this->assertSame(Locale::SERBIAN_CYRILLIC_SERBIA, Locale::default());

        locale_set_default('sr_Latn.UTF-8');
        $this->assertSame(Locale::SERBIAN_LATIN, Locale::default());
        locale_set_default('sr_Latn_RS.UTF-8');
        $this->assertSame(Locale::SERBIAN_LATIN_SERBIA, Locale::default());
    }

    public function test_default_ignores_variant(): void
    {
        locale_set_default('sr_RS@ekavsk');
        $this->assertSame(Locale::SERBIAN_SERBIA, Locale::default());

        locale_set_default('sr_Cyrl@ekavsk');
        $this->assertSame(Locale::SERBIAN_CYRILLIC, Locale::default());
        locale_set_default('sr_Cyrl_RS@ekavsk');
        $this->assertSame(Locale::SERBIAN_CYRILLIC_SERBIA, Locale::default());

        locale_set_default('sr_Latn@ekavsk');
        $this->assertSame(Locale::SERBIAN_LATIN, Locale::default());
        locale_set_default('sr_Latn_RS@ekavsk');
        $this->assertSame(Locale::SERBIAN_LATIN_SERBIA, Locale::default());
    }

    public function test_default_ignores_extension(): void
    {
        locale_set_default('sr_RS-u-currency-EUR');
        $this->assertSame(Locale::SERBIAN_SERBIA, Locale::default());

        locale_set_default('sr_Cyrl-u-currency-EUR');
        $this->assertSame(Locale::SERBIAN_CYRILLIC, Locale::default());
        locale_set_default('sr_Cyrl_RS-u-currency-EUR');
        $this->assertSame(Locale::SERBIAN_CYRILLIC_SERBIA, Locale::default());

        locale_set_default('sr_Latn-u-currency-EUR');
        $this->assertSame(Locale::SERBIAN_LATIN, Locale::default());
        locale_set_default('sr_Latn_RS-u-currency-EUR');
        $this->assertSame(Locale::SERBIAN_LATIN_SERBIA, Locale::default());
    }

    public function test_default_ignores_casing(): void
    {
        locale_set_default('ar_TN');
        $this->assertSame(Locale::ARABIC_TUNISIA, Locale::default());

        locale_set_default('AR_TN');
        $this->assertSame(Locale::ARABIC_TUNISIA, Locale::default());

        locale_set_default('AR_tn');
        $this->assertSame(Locale::ARABIC_TUNISIA, Locale::default());

        locale_set_default('aR_Tn');
        $this->assertSame(Locale::ARABIC_TUNISIA, Locale::default());

        locale_set_default('Ar_tN');
        $this->assertSame(Locale::ARABIC_TUNISIA, Locale::default());

        locale_set_default('Ar_TN');
        $this->assertSame(Locale::ARABIC_TUNISIA, Locale::default());

        locale_set_default('aR_TN');
        $this->assertSame(Locale::ARABIC_TUNISIA, Locale::default());

        locale_set_default('AR_Tn');
        $this->assertSame(Locale::ARABIC_TUNISIA, Locale::default());

        locale_set_default('AR_tN');
        $this->assertSame(Locale::ARABIC_TUNISIA, Locale::default());
    }

    public function test_fallback_to_just_language(): void
    {
        locale_set_default('zh_CN');

        $this->assertSame(Locale::CHINESE, Locale::default());
    }

    public function test_default_fallbacks_to_english(): void
    {
        locale_set_default('xx_XX');

        $this->assertSame(Locale::ENGLISH, Locale::default());
    }

    /**
     * @return Generator<string, array{Locale}, void, null>
     */
    public static function getAllLocales(): Generator
    {
        foreach (Locale::cases() as $locale) {
            yield $locale->value => [$locale];
        }

        return null;
    }

    #[DataProvider('getAllLocales')]
    public function test_it_returns_the_language_and_human_readable_name(Locale $locale): void
    {
        $display_language = $locale->getDisplayLanguage(Locale::ENGLISH);
        $language = $locale->getLanguage();
        $display_name = $locale->getDisplayName(Locale::ENGLISH);

        $this->assertNotEmpty($display_language);
        $this->assertStringContainsString($language, $locale->value);

        $this->assertStringContainsString($display_language, $display_name);

        if ($locale->hasRegion()) {
            $region = $locale->getDisplayRegion(Locale::ENGLISH);
            $region = replace_every($region, [
                '(' => '[',
                ')' => ']',
            ]);

            $this->assertStringContainsString($region, $display_name);
        }
    }

    /**
     * @return Generator<string, array{Locale}, void, null>
     */
    public static function getLocalesWithScript(): Generator
    {
        foreach (Locale::cases() as $locale) {
            if ($locale->hasScript()) {
                yield $locale->value => [$locale];
            }
        }

        return null;
    }

    #[DataProvider('getLocalesWithScript')]
    public function test_it_returns_the_script(Locale $locale): void
    {
        $this->assertTrue($locale->hasScript());
        $this->assertNotEmpty($locale->getScript());
    }

    /**
     * @return Generator<string, array{Locale}, void, null>
     */
    public static function getLocalesWithoutScript(): Generator
    {
        foreach (Locale::cases() as $locale) {
            if (! $locale->hasScript()) {
                yield $locale->value => [$locale];
            }
        }

        return null;
    }

    #[DataProvider('getLocalesWithoutScript')]
    public function test_it_does_not_returns_the_script(Locale $locale): void
    {
        $this->assertFalse($locale->hasScript());
        $this->assertNull($locale->getScript());
    }

    /**
     * @return Generator<string, array{Locale}, void, null>
     */
    public static function getLocalesWithRegion(): Generator
    {
        foreach (Locale::cases() as $locale) {
            if ($locale->hasRegion()) {
                yield $locale->value => [$locale];
            }
        }

        return null;
    }

    #[DataProvider('getLocalesWithRegion')]
    public function test_it_returns_the_region(Locale $locale): void
    {
        $this->assertTrue($locale->hasRegion());
        $this->assertNotEmpty($locale->getRegion());
        $this->assertNotEmpty($locale->getDisplayRegion());
    }

    /**
     * @return Generator<string, array{Locale}, void, null>
     */
    public static function getLocalesWithoutRegion(): Generator
    {
        foreach (Locale::cases() as $locale) {
            if (! $locale->hasRegion()) {
                yield $locale->value => [$locale];
            }
        }

        return null;
    }

    #[DataProvider('getLocalesWithoutRegion')]
    public function test_it_does_not_returns_the_region(Locale $locale): void
    {
        $this->assertFalse($locale->hasRegion());
        $this->assertNull($locale->getRegion());
        $this->assertNull($locale->getDisplayRegion());
    }
}
