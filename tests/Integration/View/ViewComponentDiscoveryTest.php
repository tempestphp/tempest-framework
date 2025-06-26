<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\View;

use Tempest\Discovery\DiscoveryItems;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\View\Components\Input;
use Tempest\View\Exceptions\ViewComponentWasAlreadyRegistered;
use Tempest\View\ViewComponentDiscovery;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class ViewComponentDiscoveryTest extends FrameworkIntegrationTestCase
{
    public function test_duplicates(): void
    {
        $discovery = $this->container->get(ViewComponentDiscovery::class);
        $discovery->setItems(new DiscoveryItems([]));

        try {
            $discovery->discoverPath(new DiscoveryLocation('', ''), __DIR__ . '/duplicateComponent.view.php');
            $discovery->apply();
        } catch (ViewComponentWasAlreadyRegistered $viewComponentWasAlreadyRegistered) {
            $this->assertStringContainsString(__DIR__ . '/duplicateComponent.view.php', $viewComponentWasAlreadyRegistered->getMessage());
            $this->assertStringContainsString(Input::class, $viewComponentWasAlreadyRegistered->getMessage());
            $this->assertStringContainsString('x-input', $viewComponentWasAlreadyRegistered->getMessage());
        }
    }

    public function test_auto_registration(): void
    {
        $discovery = $this->container->get(ViewComponentDiscovery::class);
        $discovery->setItems(new DiscoveryItems([]));
        $discovery->discoverPath(new DiscoveryLocation('', ''), __DIR__ . '/x-auto-registered.view.php');
        $discovery->apply();

        $html = $this->render(<<<'HTML'
        <x-auto-registered></x-auto-registered>
        HTML);

        $this->assertSame('<span>Hello World</span>', $html);
    }

    public function test_auto_registration_with_x_component(): void
    {
        $discovery = $this->container->get(ViewComponentDiscovery::class);
        $discovery->setItems(new DiscoveryItems([]));
        $discovery->discoverPath(new DiscoveryLocation('', ''), __DIR__ . '/x-auto-registered-with-declaration.view.php');
        $discovery->apply();

        $html = $this->render(<<<'HTML'
        <x-auto-registered-with-declaration></x-auto-registered-with-declaration>
        HTML);

        $this->assertSame('<span>Hello World</span>', $html);
    }
}
