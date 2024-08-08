<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\View;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use function Tempest\view;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class TempestViewRendererTest extends FrameworkIntegrationTestCase
{
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

    #[Test]
    #[DataProvider('provide_a_attribute_values')]
    public function it_can_render_an_a_element_with_href_attribute(string $expected, string $action, string $body): void
    {
        $this->assertSame(
            $expected,
            $this->render(view(sprintf('<a :uri="%s">%s</a>', $action, $body))),
        );
    }

    public static function provide_a_attribute_values(): Generator
    {
        yield 'invokable' => [
            '<a href="/test">Test pagina</a>',
            '\Tests\Tempest\Fixtures\Controllers\TestController::class',
            'Test pagina',
        ];

        yield 'with method' => [
            '<a href="/not-found">Page not found</a>',
            '[\Tests\Tempest\Fixtures\Controllers\TestController::class, \'notFound\']',
            'Page not found',
        ];

        yield 'with method and parameters' => [
            '<a href="/test/123/felipe">Hello Felipe</a>',
            '[\Tests\Tempest\Fixtures\Controllers\TestController::class, \'withParams\', \'123\', \'filipe\']',
            'Hello Felipe',
        ];
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
        $this->assertSame(
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
        $this->assertSame(
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
        $this->assertSame(
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
        $this->assertSame(
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
        $this->assertSame(
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
}
