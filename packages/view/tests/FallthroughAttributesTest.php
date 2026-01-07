<?php

namespace Tempest\View\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\View\Renderers\TempestViewRenderer;
use Tempest\View\ViewConfig;

use function Tempest\View\view;

final class FallthroughAttributesTest extends TestCase
{
    #[Test]
    public function render(): void
    {
        $viewConfig = new ViewConfig()->addViewComponents(
            __DIR__ . '/Fixtures/x-fallthrough-test.view.php',
            __DIR__ . '/Fixtures/x-fallthrough-dynamic-test.view.php',
        );

        $renderer =
            TempestViewRenderer::make(
                viewConfig: $viewConfig,
            );

        $html = $renderer->render(
            view(__DIR__ . '/Fixtures/fallthrough.view.php'),
        );

        $this->assertEquals(<<<'HTML'
        <div class="in-component component-class"></div>
        <div class="in-component component-class"></div>
        <div class="component-class" style="display: block;"></div>
        <div class="component-class" style="display: block;"></div>
        HTML, $html);
    }
}
