<?php

namespace Tests\Tempest\Integration\Intl;

use Tempest\Discovery\DiscoveryItems;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Intl\IntlConfig;
use Tempest\Intl\Locale;
use Tempest\Intl\TranslationMessageDiscovery;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class DiscoveryTest extends FrameworkIntegrationTestCase
{
    public function test_functions_are_discovered(): void
    {
        $config = $this->container->get(IntlConfig::class);

        $this->assertCount(4, $config->functions);
    }

    public function test_markup_formatters_are_discovered(): void
    {
        $config = $this->container->get(IntlConfig::class);

        $this->assertCount(3, $config->markupFormatters);
    }

    public function test_discovery_adds_paths_to_config(): void
    {
        $this->container->config(new IntlConfig(
            currentLocale: Locale::default(),
            fallbackLocale: Locale::default(),
        ));

        $discovery = $this->container->get(TranslationMessageDiscovery::class);
        $discovery->setItems(new DiscoveryItems([]));
        $discovery->discoverPath(new DiscoveryLocation('', ''), __DIR__ . '/Fixtures/messages.json');
        $discovery->discoverPath(new DiscoveryLocation('', ''), __DIR__ . '/Fixtures/messages.abcde.json');
        $discovery->discoverPath(new DiscoveryLocation('', ''), __DIR__ . '/Fixtures/messages.fr.yaml');
        $discovery->discoverPath(new DiscoveryLocation('', ''), __DIR__ . '/Fixtures/messages.en_US.json');
        $discovery->apply();

        $config = $this->container->get(IntlConfig::class);

        $this->assertSame(
            expected: [
                'fr' => [__DIR__ . '/Fixtures/messages.fr.yaml'],
                'en_US' => [__DIR__ . '/Fixtures/messages.en_US.json'],
            ],
            actual: $config->translationMessagePaths,
        );
    }
}
