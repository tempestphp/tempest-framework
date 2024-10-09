<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\View;

use function Tempest\view;
use Tempest\View\ViewCache;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class TempestViewRendererTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->container->get(ViewCache::class)->clear();
    }

    public function test_view_renderer(): void
    {
        $this->assertSame(
            '<h1>Hello</h1>',
            $this->render('<h1>Hello</h1>'),
        );

        $this->assertSame(
            '<h1>Hello</h1>',
            $this->render(view('<h1>{{ $this->foo }}</h1>')->data(foo: 'Hello')),
        );

        $this->assertSame(
            '<h1></h1>',
            $this->render(view('<h1>{{ $this->foo }}</h1>')),
        );

        $this->assertSame(
            '<h1>Hello</h1>',
            $this->render(view('<h1>{{ $this->raw("foo") }}</h1>')->data(foo: 'Hello')),
        );
    }

    public function test_if_attribute(): void
    {
        $this->assertSame(
            '',
            $this->render(view('<div :if="$this->show">Hello</div>')->data(show: false)),
        );

        $this->assertSame(
            '<div :if="$this->show">Hello</div>',
            $this->render(view('<div :if="$this->show">Hello</div>')->data(show: true)),
        );
    }

    public function test_elseif_attribute(): void
    {
        $this->assertSame(
            '<div :if="$this->a">A</div>',
            $this->render(view('<div :if="$this->a">A</div><div :elseif="$this->b">B</div><div :else>None</div>')->data(a: true, b: true)),
        );

        $this->assertSame(
            '<div :if="$this->a">A</div>',
            $this->render(view('<div :if="$this->a">A</div><div :elseif="$this->b">B</div><div :else>None</div>')->data(a: true, b: false)),
        );

        $this->assertSame(
            '<div :elseif="$this->b">B</div>',
            $this->render(view('<div :if="$this->a">A</div><div :elseif="$this->b">B</div><div :else>None</div>')->data(a: false, b: true)),
        );

        $this->assertSame(
            '<div :else>None</div>',
            $this->render(view('<div :if="$this->a">A</div><div :elseif="$this->b">B</div><div :else>None</div>')->data(a: false, b: false)),
        );

        $this->assertSame(
            '<div :elseif="$this->c">C</div>',
            $this->render(view('<div :if="$this->a">A</div><div :elseif="$this->b">B</div><div :elseif="$this->c">C</div><div :else>None</div>')->data(a: false, b: false, c: true)),
        );

        $this->assertSame(
            '<div :elseif="$this->b">B</div>',
            $this->render(view('<div :if="$this->a">A</div><div :elseif="$this->b">B</div><div :elseif="$this->c">C</div><div :else>None</div>')->data(a: false, b: true, c: true)),
        );

        $this->assertSame(
            '<div :else>None</div>',
            $this->render(view('<div :if="$this->a">A</div><div :elseif="$this->b">B</div><div :elseif="$this->c">C</div><div :else>None</div>')->data(a: false, b: false, c: false)),
        );
    }

    public function test_else_attribute(): void
    {
        $this->assertSame(
            '<div :if="$this->show">True</div>',
            $this->render(view('<div :if="$this->show">True</div><div :else>False</div>')->data(show: true)),
        );

        $this->assertSame(
            '<div :else>False</div>',
            $this->render(view('<div :if="$this->show">True</div><div :else>False</div>')->data(show: false)),
        );
    }

    public function test_foreach_attribute(): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'HTML'
            <div :foreach="$this->items as $foo">a</div>
            <div :foreach="$this->items as $foo">b</div>
            HTML,
            $this->render(view('<div :foreach="$this->items as $foo">{{ $foo }}</div>')->data(items: ['a', 'b'])),
        );
    }

    public function test_forelse_attribute(): void
    {
        $this->assertSame(
            <<<'HTML'
            <div :forelse>Empty</div>
            HTML,
            $this->render(view('<div :foreach="$this->items as $foo">{{ $foo }}</div><div :forelse>Empty</div>')->data(items: [])),
        );

        $this->assertSame(
            <<<'HTML'
            <div :foreach="$this->items as $foo">a</div>
            HTML,
            $this->render(view('<div :foreach="$this->items as $foo">{{ $foo }}</div><div :forelse>Empty</div>')->data(items: ['a'])),
        );
    }

    public function test_default_slot(): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'HTML'
            <div class="base">
                
                    Test
                
            </div>
            HTML,
            $this->render(
                <<<'HTML'
                <x-base-layout>
                    <x-slot>
                        Test
                    </x-slot>
                </x-base-layout>
                HTML,
            ),
        );
    }

    public function test_implicit_default_slot(): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'HTML'
            <div class="base">
                
                Test
            
            </div>
            HTML,
            $this->render(
                <<<'HTML'
                <x-base-layout>
                    Test
                </x-base-layout>
                HTML,
            ),
        );
    }

    public function test_multiple_slots(): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'HTML'
            injected scripts
                
            
            <div class="base">
                
                Test
                
                
            
                
                Hi
            
            </div>
            
            
                injected styles
            HTML,
            $this->render(
                <<<'HTML'
            <x-complex-base>
                Test
                
                <x-slot name="scripts">
                injected scripts
                </x-slot>
                
                <x-slot name="styles">
                injected styles
                </x-slot>
                
                Hi
            </x-complex-base>
            HTML,
            ),
        );
    }

    public function test_pre(): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'HTML'
            <pre>
            a
                    <span class="hl-prop">b</span>
               <span class="hl-type">c</span>
            </pre>
            HTML,
            $this->render(
                <<<'HTML'
            <pre>
            a
                    <span class="hl-prop">b</span>
               <span class="hl-type">c</span>
            </pre>
            HTML,
            ),
        );
    }

    public function test_use_statements_are_grouped(): void
    {
        $html = $this->render('<x-view-component-with-use-import></x-view-component-with-use-import><x-view-component-with-use-import></x-view-component-with-use-import>');

        $this->assertStringContainsString('/', $html);
    }
    
    public function test_raw_and_escaped(): void
    {
        $html = $this->render(view(__DIR__ . '/../../Fixtures/Views/raw-escaped.view.php', var: '<h1>hi</h1>'));

        $this->assertSame(<<<'HTML'
        &lt;h1&gt;hi&lt;/h1&gt;
        &lt;H1&gt;HI&lt;/H1&gt;
        <h1>hi</h1>
        HTML, $html);
    }
}
