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
final class ViewComponentDiscoveryTest extends FrameworkIntegrationTestCase
{
    public function test_duplicates(): void
    {
        $discovery = $this->container->get(ViewComponentDiscovery::class);

        try {
            $discovery->discoverPath(__DIR__ . '/duplicateComponent.view.php');
        } catch (DuplicateViewComponent $duplicateViewComponent) {
            $this->assertStringContainsString(__DIR__ . '/duplicateComponent.view.php', $duplicateViewComponent->getMessage());
            $this->assertStringContainsString(Input::class, $duplicateViewComponent->getMessage());
            $this->assertStringContainsString('x-input', $duplicateViewComponent->getMessage());
        }
    }
}
