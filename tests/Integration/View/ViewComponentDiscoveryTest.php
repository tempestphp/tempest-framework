<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\View;

use Tempest\Discovery\DiscoveryItems;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\View\Exceptions\ViewComponentWasAlreadyRegistered;
use Tempest\View\ViewComponent;
use Tempest\View\ViewComponentDiscovery;
use Tempest\View\ViewConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class ViewComponentDiscoveryTest extends FrameworkIntegrationTestCase
{
    public function test_vendor_components_get_overwritten(): void
    {
        /** @var ViewConfig $viewConfig */
        $viewConfig = $this->get(ViewConfig::class);

        $viewConfig->addViewComponent(new ViewComponent(
            name: 'x-form',
            contents: 'overwritten',
            file: '',
            isVendorComponent: false,
        ));

        $this->assertSame('overwritten', $this->view->render('<x-form />'));
    }

    public function test_project_view_components_cannot_be_overwritten_by_other_project_view_component(): void
    {
        /** @var ViewConfig $viewConfig */
        $viewConfig = $this->get(ViewConfig::class);

        $viewConfig->addViewComponent(new ViewComponent(
            name: 'x-form',
            contents: 'overwritten',
            file: '',
            isVendorComponent: false,
        ));

        $this->assertException(
            ViewComponentWasAlreadyRegistered::class,
            function () use ($viewConfig): void {
                $viewConfig->addViewComponent(new ViewComponent(
                    name: 'x-form',
                    contents: 'b',
                    file: '',
                    isVendorComponent: false,
                ));
            },
        );
    }

    public function test_project_view_components_will_not_be_overwritten_by_vendor_view_component(): void
    {
        /** @var ViewConfig $viewConfig */
        $viewConfig = $this->get(ViewConfig::class);

        $viewConfig->addViewComponent(new ViewComponent(
            name: 'x-form',
            contents: 'overwritten',
            file: '',
            isVendorComponent: false,
        ));

        $viewConfig->addViewComponent(new ViewComponent(
            name: 'x-form',
            contents: 'original',
            file: '',
            isVendorComponent: true,
        ));

        $this->assertSame('overwritten', $this->view->render('<x-form />'));
    }

    public function test_auto_registration(): void
    {
        $discovery = $this->container->get(ViewComponentDiscovery::class);
        $discovery->setItems(new DiscoveryItems([]));
        $discovery->discoverPath(new DiscoveryLocation('', ''), __DIR__ . '/x-auto-registered.view.php');
        $discovery->apply();

        $html = $this->view->render(<<<'HTML'
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

        $html = $this->view->render(<<<'HTML'
        <x-auto-registered-with-declaration></x-auto-registered-with-declaration>
        HTML);

        $this->assertSame('<span>Hello World</span>', $html);
    }
}
