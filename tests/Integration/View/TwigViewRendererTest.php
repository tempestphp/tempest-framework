<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\View;

use PHPUnit\Framework\Attributes\CoversNothing;
use Tempest\View\Renderers\TwigConfig;
use Tempest\View\Renderers\TwigViewRenderer;
use Tempest\View\ViewConfig;
use Tempest\View\ViewRenderer;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\view;

/**
 * @internal
 */
#[CoversNothing]
final class TwigViewRendererTest extends FrameworkIntegrationTestCase
{
    public function test_twig(): void
    {
        $viewConfig = $this->container->get(ViewConfig::class);

        $viewConfig->rendererClass = TwigViewRenderer::class;

        $this->container->config(new TwigConfig(
            viewPaths: [__DIR__ . '/twig'],
            cachePath: 'twig-cache',
        ));

        $renderer = $this->container->get(ViewRenderer::class);

        $html = $renderer->render(view('index.twig', ...['foo' => 'bar']));

        $this->assertStringEqualsStringIgnoringLineEndings(<<<HTML
        <html>
        <span>bar</span>
        </html>
        HTML, $html);
    }
}
