<?php

namespace Tempest\Intl\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\Intl\Catalog\GenericCatalog;
use Tempest\Intl\Locale;

final class GenericCatalogTest extends TestCase
{
    public function test_basic(): void
    {
        $catalog = new GenericCatalog();
        $catalog->add(Locale::FRENCH, 'hello', 'Bonjour');

        // Has test
        $this->assertTrue($catalog->has(Locale::FRENCH, 'hello'));
        $this->assertFalse($catalog->has(Locale::FRENCH, 'goodbye'));
        $this->assertFalse($catalog->has(Locale::ENGLISH, 'hello'));

        // Get test
        $this->assertSame('Bonjour', $catalog->get(Locale::FRENCH, 'hello'));

        // Fallback test
        $this->assertSame('Bonjour', $catalog->get(Locale::FRENCH_FRANCE, 'hello'));
    }
}
