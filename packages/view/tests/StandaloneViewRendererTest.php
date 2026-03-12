<?php

namespace Tempest\View\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\View\Exceptions\ViewCompilationFailed;
use Tempest\View\Exceptions\ViewComponentPathWasInvalid;
use Tempest\View\Exceptions\ViewComponentPathWasNotFound;
use Tempest\View\Exceptions\XmlDeclarationCouldNotBeParsed;
use Tempest\View\Renderers\TempestViewRenderer;
use Tempest\View\ViewCache;
use Tempest\View\ViewComponent;
use Tempest\View\ViewConfig;

use function Tempest\View\view;

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

    public function test_with_cache_enabled(): void
    {
        $viewCache = ViewCache::create();
        $viewCache->clear();

        $renderer =
            TempestViewRenderer::make(
                viewCache: $viewCache,
            );

        $html = $renderer->render(
            view(__DIR__ . '/Fixtures/standalone.view.php'),
        );

        $this->assertSnippetsMatch(<<<'HTML'
        <x-standalone-base>
            Hi
        </x-standalone-base>
        HTML, $html);
    }

    public function test_with_cache_disabled(): void
    {
        $renderer = TempestViewRenderer::make(
            viewCache: ViewCache::create(enabled: false),
        );

        $html = $renderer->render(
            view(__DIR__ . '/Fixtures/standalone.view.php'),
        );

        $this->assertSnippetsMatch(<<<'HTML'
        <x-standalone-base>
            Hi
        </x-standalone-base>
        HTML, $html);
    }

    public function test_xml_declaration_with_short_open_tag(): void
    {
        if (! ini_get('short_open_tag')) {
            $this->markTestSkipped('This test requires short_open_tag to be enabled.');
        }

        $this->expectException(XmlDeclarationCouldNotBeParsed::class);

        $renderer = TempestViewRenderer::make();
        $renderer->render('<?xml version="1.0" encoding="UTF-8" ?><test></test>');
    }

    #[Test]
    public function test_maps_source_path_and_line_for_view_errors(): void
    {
        $renderer = TempestViewRenderer::make();

        try {
            $renderer->render(view(__DIR__ . '/Fixtures/standalone-error.view.php'));
            $this->fail('Expected a view compilation exception.');
        } catch (ViewCompilationFailed $exception) {
            $this->assertSame(__DIR__ . '/Fixtures/standalone-error.view.php', $exception->sourcePath);
            $this->assertSame(2, $exception->sourceLine);
        }
    }

    #[Test]
    public function test_maps_source_path_and_line_for_undefined_variable_errors(): void
    {
        $renderer = TempestViewRenderer::make();

        try {
            $renderer->render(view(__DIR__ . '/Fixtures/standalone-undefined-variable.view.php'));
            $this->fail('Expected a view compilation exception.');
        } catch (ViewCompilationFailed $exception) {
            $this->assertSame(__DIR__ . '/Fixtures/standalone-undefined-variable.view.php', $exception->sourcePath);
            $this->assertSame(2, $exception->sourceLine);
        }
    }

    #[Test]
    public function test_maps_source_path_and_line_for_component_errors(): void
    {
        $viewConfig = new ViewConfig()->addViewComponents(
            __DIR__ . '/Fixtures/x-standalone-error-component.view.php',
        );

        $renderer =
            TempestViewRenderer::make(
                viewConfig: $viewConfig,
            );

        try {
            $renderer->render(view(__DIR__ . '/Fixtures/standalone-error-component-usage.view.php'));
            $this->fail('Expected a view compilation exception.');
        } catch (ViewCompilationFailed $exception) {
            $this->assertSame(__DIR__ . '/Fixtures/x-standalone-error-component.view.php', $exception->sourcePath);
            $this->assertSame(2, $exception->sourceLine);
        }
    }

    #[Test]
    public function test_maps_source_path_and_line_for_slot_content_errors(): void
    {
        $viewConfig = new ViewConfig()->addViewComponents(
            __DIR__ . '/Fixtures/x-standalone-base.view.php',
        );

        $renderer =
            TempestViewRenderer::make(
                viewConfig: $viewConfig,
            );

        try {
            $renderer->render(view(__DIR__ . '/Fixtures/standalone-error-slot-usage.view.php'));
            $this->fail('Expected a view compilation exception.');
        } catch (ViewCompilationFailed $exception) {
            $this->assertSame(__DIR__ . '/Fixtures/standalone-error-slot-usage.view.php', $exception->sourcePath);
            $this->assertSame(2, $exception->sourceLine);
        }
    }

    #[Test]
    public function test_maps_source_path_and_line_for_expression_attribute_errors_after_component_rendering(): void
    {
        $viewConfig = new ViewConfig()->addViewComponents(
            __DIR__ . '/Fixtures/x-standalone-base.view.php',
        );

        $renderer =
            TempestViewRenderer::make(
                viewConfig: $viewConfig,
            );

        try {
            $renderer->render(view(__DIR__ . '/Fixtures/standalone-attribute-error.view.php'));
            $this->fail('Expected a view compilation exception.');
        } catch (ViewCompilationFailed $exception) {
            $this->assertSame(__DIR__ . '/Fixtures/standalone-attribute-error.view.php', $exception->sourcePath);
            $this->assertSame(6, $exception->sourceLine);
        }
    }

    protected function assertSnippetsMatch(string $expected, string $actual): void
    {
        $expected = str_replace([PHP_EOL, ' '], '', $expected);
        $actual = str_replace([PHP_EOL, ' '], '', $actual);

        $this->assertSame($expected, $actual);
    }
}
