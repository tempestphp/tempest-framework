<?php

namespace Tests\Tempest\Integration\Core;

use Tempest\Support\Namespace\Psr4Namespace;
use Tempest\View\ViewComponent;
use Tempest\View\ViewConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Support\arr;

final class ViewComponentsInstallerTest extends FrameworkIntegrationTestCase
{
    private int $searchOptionCount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->installer
            ->configure(
                $this->internalStorage . '/install',
                new Psr4Namespace('App\\', $this->internalStorage . '/install/App'),
            )
            ->setRoot($this->internalStorage . '/install');

        $this->view->registerViewComponent(
            name: 'x-vendor-a',
            html: 'vendor a',
            file: __DIR__ . '/Fixtures/x-vendor-a.view.php',
            isVendor: true,
        );

        $this->view->registerViewComponent(
            name: 'x-vendor-b',
            html: 'vendor b',
            file: __DIR__ . '/Fixtures/x-vendor-b.view.php',
            isVendor: true,
        );

        $this->searchOptionCount = arr($this->get(ViewConfig::class)->viewComponents)
            ->filter(fn (mixed $input) => $input instanceof ViewComponent)
            ->filter(fn (ViewComponent $viewComponent) => $viewComponent->isVendorComponent)
            ->count();
    }

    protected function tearDown(): void
    {
        $this->installer->clean();

        parent::tearDown();
    }

    public function test_all_vendor_view_components_are_listed(): void
    {
        $this->console
            ->call('install view-components --force')
            ->assertSee('x-vendor-a')
            ->assertSee('x-vendor-b')
            ->submit(($this->searchOptionCount - 2) . ', ' . ($this->searchOptionCount - 1));

        $this->installer
            ->assertFileExists(
                path: 'App/ViewComponents/x-vendor-a.view.php',
                content: 'vendor a',
            )
            ->assertFileExists(
                path: 'App/ViewComponents/x-vendor-b.view.php',
                content: 'vendor b',
            );
    }

    public function test_installed_vendor_components_are_not_listed_anymore(): void
    {
        $this->view->registerViewComponent(
            name: 'x-vendor-b',
            html: 'vendor b',
            file: __DIR__ . '/Fixtures/x-vendor-b.view.php',
        );

        $this->console
            ->call('install view-components --force')
            ->assertSee('x-vendor-a')
            ->assertNotSee('x-vendor-b')
            ->submit($this->searchOptionCount - 2);
    }
}
