<?php

declare(strict_types=1);

namespace Tempest\View\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\View\Renderers\TempestViewRenderer;
use Tempest\View\ViewConfig;

use function Tempest\View\view;

final class AsAttributeTest extends TestCase
{
    #[Test]
    public function generic_element_static_as_overrides_tag(): void
    {
        $html = TempestViewRenderer::make()->render(
            '<a as="button"><span>Test</span></a>',
        );

        $this->assertSnippetsMatch('<button><span>Test</span></button>', $html);
    }

    #[Test]
    public function generic_element_expression_as_overrides_tag(): void
    {
        $html = TempestViewRenderer::make()->render(
            view('<a :as="$tag"><span>Test</span></a>')->data(tag: 'button'),
        );

        $this->assertSnippetsMatch('<button><span>Test</span></button>', $html);
    }

    #[Test]
    public function generic_element_static_as_on_nested_element(): void
    {
        $html = TempestViewRenderer::make()->render(
            '<div><a as="button"><span>Test</span></a></div>',
        );

        $this->assertSnippetsMatch('<div><button><span>Test</span></button></div>', $html);
    }

    #[Test]
    public function generic_element_expression_as_on_nested_element(): void
    {
        $html = TempestViewRenderer::make()->render(
            view('<div><a :as="$tag"><span>Test</span></a></div>')->data(tag: 'button'),
        );

        $this->assertSnippetsMatch('<div><button><span>Test</span></button></div>', $html);
    }

    #[Test]
    public function view_component_static_as_overrides_root_tag(): void
    {
        $renderer = $this->makeRenderer();

        $html = $renderer->render(
            '<x-link as="button"><span>Test</span></x-link>',
        );

        $this->assertSnippetsMatch('<button><span>Test</span></button>', $html);
    }

    #[Test]
    public function view_component_expression_as_defaults_to_button_when_no_href(): void
    {
        $renderer = $this->makeRenderer();

        $html = $renderer->render(
            view('<x-link :as="$href ? \'a\' : \'button\'"><span>Test</span></x-link>')->data(href: null),
        );

        $this->assertSnippetsMatch('<button><span>Test</span></button>', $html);
    }

    #[Test]
    public function view_component_expression_as_resolves_to_a_when_href_is_set(): void
    {
        $renderer = $this->makeRenderer();

        $html = $renderer->render(
            view('<x-link :as="$href ? \'a\' : \'button\'"><span>Test</span></x-link>')->data(href: 'https://example.com'),
        );

        $this->assertSnippetsMatch('<a><span>Test</span></a>', $html);
    }

    #[Test]
    public function view_component_with_static_as_inside_generic_div(): void
    {
        $renderer = $this->makeRenderer();

        $html = $renderer->render(
            '<div><x-link as="button"><span>Test</span></x-link></div>',
        );

        $this->assertSnippetsMatch('<div><button><span>Test</span></button></div>', $html);
    }

    #[Test]
    public function view_component_without_as_wrapping_component_with_static_as(): void
    {
        $renderer = $this->makeRenderer();

        $html = $renderer->render(<<<'HTML'
            <x-outer>
                <x-link as="button"><span>Test</span></x-link>
            </x-outer>
        HTML);

        $this->assertSnippetsMatch('<section><button><span>Test</span></button></section>', $html);
    }

    #[Test]
    public function view_component_without_as_wrapping_component_with_expression_as(): void
    {
        $renderer = $this->makeRenderer();

        $html = $renderer->render(
            view(<<<'HTML'
                <x-outer>
                    <x-link :as="$tag ?? 'button'"><span>Test</span></x-link>
                </x-outer>
            HTML)->data(tag: null),
        );

        $this->assertSnippetsMatch('<section><button><span>Test</span></button></section>', $html);
    }

    #[Test]
    public function view_component_without_as_wrapping_component_with_expression_as_resolved_to_a(): void
    {
        $renderer = $this->makeRenderer();

        $html = $renderer->render(
            view(<<<'HTML'
                <x-outer>
                    <x-link :as="$tag ?? 'button'"><span>Test</span></x-link>
                </x-outer>
            HTML)->data(tag: 'a'),
        );

        $this->assertSnippetsMatch('<section><a><span>Test</span></a></section>', $html);
    }

    private function makeRenderer(): TempestViewRenderer
    {
        $viewConfig = new ViewConfig()->addViewComponents(
            __DIR__ . '/Fixtures/x-link.view.php',
            __DIR__ . '/Fixtures/x-outer.view.php',
        );

        return TempestViewRenderer::make(viewConfig: $viewConfig);
    }

    private function assertSnippetsMatch(string $expected, string $actual): void
    {
        $this->assertSame(
            str_replace([PHP_EOL, ' '], '', $expected),
            str_replace([PHP_EOL, ' '], '', $actual),
        );
    }
}
