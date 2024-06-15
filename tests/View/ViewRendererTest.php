<?php

namespace Tests\Tempest\View;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tempest\View\View;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use function Tempest\view;

class ViewRendererTest extends FrameworkIntegrationTestCase
{
    #[DataProvider('data')]
    public function test_view_renderer(string|View $input, string $expected): void
    {
        $this->assertSame($expected, $this->render($input));
    }

    public static function data(): Generator
    {
        yield ['<h1>Hello</h1>', '<h1>Hello</h1>'];

        yield [
            view('<h1>{{ $this->foo }}</h1>')->data(foo: 'Hello'),
            '<h1>Hello</h1>',
        ];

        yield [
            view('<h1>{{ $this->foo }}</h1>'),
            '<h1></h1>',
        ];

        yield [
            view('<h1>{{ $this->raw("foo") }}</h1>')->data(foo: 'Hello'),
            '<h1>Hello</h1>',
        ];
    }
}