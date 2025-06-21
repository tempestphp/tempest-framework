<?php

namespace Tests\Tempest\Integration\Intl;

use Tempest\EventBus\EventBus;
use Tempest\Intl\Catalog\Catalog;
use Tempest\Intl\IntlConfig;
use Tempest\Intl\Locale;
use Tempest\Intl\TranslationFailure;
use Tempest\Intl\TranslationMiss;
use Tempest\Intl\Translator;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Intl\translate;
use function Tempest\Intl\translate_locale;

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

    public function test_event_miss(): void
    {
        /** @var TranslationMiss|null $received */
        $received = null;

        $eventbus = $this->container->get(EventBus::class);
        $eventbus->listen(TranslationMiss::class, function (TranslationMiss $event) use (&$received): void {
            $received = $event;
        });

        $translator = $this->container->get(Translator::class);
        $translator->translate('unknown');

        $this->assertInstanceOf(TranslationMiss::class, $received);
        $this->assertSame(Locale::ENGLISH_UNITED_STATES, $received->locale);
        $this->assertSame('unknown', $received->key);
    }

    public function test_event_fail(): void
    {
        /** @var TranslationFailure|null $received */
        $received = null;

        $eventbus = $this->container->get(EventBus::class);
        $eventbus->listen(TranslationFailure::class, function (TranslationFailure $event) use (&$received): void {
            $received = $event;
        });

        $catalog = $this->container->get(Catalog::class);
        $catalog->add(Locale::ENGLISH_UNITED_STATES, 'failure', '{$foo');

        $translator = $this->container->get(Translator::class);
        $translator->translate('failure');

        $this->assertInstanceOf(TranslationFailure::class, $received);
        $this->assertSame(Locale::ENGLISH_UNITED_STATES, $received->locale);
        $this->assertSame('failure', $received->key);
        $this->assertSame('Failed to parse message.', $received->exception->getMessage());
    }
}
