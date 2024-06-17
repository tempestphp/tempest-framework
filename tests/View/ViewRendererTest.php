<?php

namespace Tests\Tempest\View;

use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use function Tempest\view;

class ViewRendererTest extends FrameworkIntegrationTestCase
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

    public function test_if_attribute()
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
}