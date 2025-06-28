<?php

namespace Tempest\Intl\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\Intl\Currency;

use function Tempest\Support\str;

final class CurrencyTest extends TestCase
{
    public function test_parse(): void
    {
        $this->assertSame(Currency::EUR, Currency::parse('EUR'));
        $this->assertSame(Currency::EUR, Currency::parse(' EUR   '));
        $this->assertSame(Currency::EUR, Currency::parse('eur'));
        $this->assertSame(Currency::EUR, Currency::parse(' eur  '));
        $this->assertSame(Currency::EUR, Currency::parse(Currency::EUR));
        $this->assertSame(Currency::EUR, Currency::parse(str('eur')));
    }
}
