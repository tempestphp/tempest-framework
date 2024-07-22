<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\View;

use Tempest\View\Components\Input;
use Tempest\View\Exceptions\DuplicateViewComponent;
use Tempest\View\ViewComponentDiscovery;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class ViewComponentDiscoveryTest extends FrameworkIntegrationTestCase
{
    public function test_duplicates(): void
    {
        $discovery = $this->container->get(ViewComponentDiscovery::class);

        try {
            $discovery->discover(__DIR__ . '/duplicateComponent.view.php');
        } catch (DuplicateViewComponent $e) {
            $this->assertStringContainsString(__DIR__ . '/duplicateComponent.view.php', $e->getMessage());
            $this->assertStringContainsString(Input::class, $e->getMessage());
            $this->assertStringContainsString('x-input', $e->getMessage());
        }
    }
}
