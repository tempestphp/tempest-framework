<?php

namespace Tempest\View\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\View\Exceptions\ViewComponentPathWasInvalid;
use Tempest\View\Exceptions\ViewComponentPathWasNotFound;
use Tempest\View\Renderers\TempestViewRenderer;
use Tempest\View\ViewComponent;
use Tempest\View\ViewConfig;

use function Tempest\view;

final class StandaloneViewRendererTest extends TestCase
{
    public function test_render(): void
    {
        $viewConfig = new ViewConfig()->addViewComponents(
            __DIR__ . '/Fixtures/x-standalone-base.view.php',
        );

        $renderer =
            TempestViewRenderer::make(
                viewConfig: $viewConfig,
            );

        $html = $renderer->render(
            view(__DIR__ . '/Fixtures/standalone.view.php'),
        );

        $this->assertSnippetsMatch(<<<'HTML'
        <div>
            Hi
        </div>
        HTML, $html);
    }

    public function test_invalid_view_component_paths(): void
    {
        try {
            ViewComponent::fromPath('component.view.php');
        } catch (ViewComponentPathWasInvalid $e) {
            $this->assertStringContainsString('component.view.php', $e->getMessage());
        }

        try {
            ViewComponent::fromPath('x-component.php');
        } catch (ViewComponentPathWasInvalid $e) {
            $this->assertStringContainsString('x-component.php', $e->getMessage());
        }

        try {
            ViewComponent::fromPath('x-missing.view.php');
        } catch (ViewComponentPathWasNotFound $e) {
            $this->assertStringContainsString('x-missing.view.php', $e->getMessage());
        }
    }

    public function test_invalid_view_component_paths_within_config(): void
    {
        try {
            new ViewConfig()->addViewComponents(
                __DIR__ . '/Fixtures/standalone-base.view.php',
            );
        } catch (ViewComponentPathWasInvalid $e) {
            $this->assertStringContainsString('standalone-base.view.php', $e->getMessage());
        }
    }

    protected function assertSnippetsMatch(string $expected, string $actual): void
    {
        $expected = str_replace([PHP_EOL, ' '], '', $expected);
        $actual = str_replace([PHP_EOL, ' '], '', $actual);

        $this->assertSame($expected, $actual);
    }
}
