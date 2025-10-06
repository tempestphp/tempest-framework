<?php

namespace Tests\Tempest\Integration\Intl;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Intl\IntlConfig;
use Tempest\Intl\Locale;
use Tempest\Intl\Translator;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class CatalogTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function basic_xliff(): void
    {
        $this->container
            ->get(IntlConfig::class)
            ->addCatalogFile(__DIR__ . '/Fixtures/messages.catalog.xliff');

        $translator = $this->container->get(Translator::class);

        $this->assertSame(
            expected: 'Il y a 4 articles dans votre panier.',
            actual: $translator->translateForLocale(Locale::FRENCH, 'cart.items', count: 4),
        );
    }
}
