<?php

namespace Tests\Tempest\Integration\View;

use Tempest\View\Components\Input;
use Tempest\View\Exceptions\DuplicateViewComponent;
use Tempest\View\ViewComponentDiscovery;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

class ViewComponentDiscoveryTest extends FrameworkIntegrationTestCase
{
    public function test_duplicates(): void
    {
        $discovery = $this->container->get(ViewComponentDiscovery::class);

        try {
            $discovery->discover(__DIR__ . '/duplicateComponent.view.php');
        } catch (DuplicateViewComponent $e) {
            $this->assertStringContainsString(__DIR__ . '/duplicateComponent.view.php', $e);
            $this->assertStringContainsString(Input::class, $e);
            $this->assertStringContainsString('x-input', $e);
        }
    }
}
