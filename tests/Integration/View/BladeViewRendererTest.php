<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\View;

use Tempest\Drift\FrameworkIntegrationTestCase;
use Tempest\View\Renderers\BladeConfig;
use Tempest\View\Renderers\BladeViewRenderer;
use Tempest\View\ViewConfig;
use Tempest\View\ViewRenderer;

use function Tempest\view;

/**
 * @internal
 */
final class BladeViewRendererTest extends FrameworkIntegrationTestCase
{
    public function test_blade(): void
    {
        $viewConfig = $this->container->get(ViewConfig::class);

        $viewConfig->rendererClass = BladeViewRenderer::class;

        $this->container->config(new BladeConfig(
            viewPaths: [__DIR__ . '/blade'],
            cachePath: 'blade-cache',
        ));

        $renderer = $this->container->get(ViewRenderer::class);

        $html = $renderer->render(view('index'));

        $this->assertSame(<<<HTML
        <html>
        Hi
        </html>

        HTML, $html);
    }
}
