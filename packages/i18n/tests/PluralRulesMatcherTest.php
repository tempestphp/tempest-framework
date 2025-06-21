<?php

namespace Tempest\Internationalization\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\Internationalization\PluralRules\PluralRulesMatcher;
use Tempest\Support\Language\Locale;

final class PluralRulesMatcherTest extends TestCase
{
    public function test_en(): void
    {
        $matcher = new PluralRulesMatcher();

        $this->assertSame('one', $matcher->getPluralCategory(Locale::ENGLISH, 1));
        $this->assertSame('other', $matcher->getPluralCategory(Locale::ENGLISH, 3));
        $this->assertSame('other', $matcher->getPluralCategory(Locale::ENGLISH, 11));
    }

    public function test_ru(): void
    {
        $matcher = new PluralRulesMatcher();

        $this->assertSame('one', $matcher->getPluralCategory(Locale::RUSSIAN, 1));
        $this->assertSame('few', $matcher->getPluralCategory(Locale::RUSSIAN, 3));
        $this->assertSame('many', $matcher->getPluralCategory(Locale::RUSSIAN, 11));
    }

    public function test_fr(): void
    {
        $matcher = new PluralRulesMatcher();

        $this->assertSame('one', $matcher->getPluralCategory(Locale::FRENCH, 1));
        $this->assertSame('many', $matcher->getPluralCategory(Locale::FRENCH, 1_000_000));
        $this->assertSame('other', $matcher->getPluralCategory(Locale::FRENCH, 5));
    }
}
