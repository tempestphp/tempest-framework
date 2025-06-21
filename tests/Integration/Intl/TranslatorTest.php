<?php

namespace Tests\Tempest\Integration\Intl;

use Tempest\Core\Commands\DiscoveryClearCommand;
use Tempest\Core\DiscoveryCache;
use Tempest\Core\FrameworkKernel;
use Tempest\Core\Kernel\LoadDiscoveryClasses;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Intl\Catalog\Catalog;
use Tempest\Intl\IntlConfig;
use Tempest\Intl\Locale;
use Tempest\Intl\Translator;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Intl\translate;
use function Tempest\Intl\translate_locale;
use function Tempest\Support\Path\normalize;

final class TranslatorTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $config = $this->container->get(IntlConfig::class);
        $config->addTranslationMessageFile(Locale::FRENCH, __DIR__ . '/Fixtures/messages.fr.json');
        $config->addTranslationMessageFile(Locale::ENGLISH, __DIR__ . '/Fixtures/messages.en_US.json');
    }

    public function test_translator(): void
    {
        $translator = $this->container->get(Translator::class);

        $this->assertSame('Hello, Jon Doe!', $translator->translate('hello', name: 'Jon Doe'));
        $this->assertSame('Project', $translator->translate('ui.sidebar.project'));
        $this->assertSame('Projet', $translator->translateForLocale(Locale::FRENCH, 'ui.sidebar.project'));
    }

    public function test_function(): void
    {
        $this->assertSame('Hello, Jon Doe!', translate('hello', name: 'Jon Doe'));
        $this->assertSame('Project', translate('ui.sidebar.project'));
        $this->assertSame('Projet', translate_locale(Locale::FRENCH, 'ui.sidebar.project'));
    }

    public function test_default_locale(): void
    {
        $config = $this->container->get(IntlConfig::class);

        $this->assertSame(Locale::default(), $config->currentLocale);
    }
}
