<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\View;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tempest\Http\Session\Session;
use Tempest\Validation\Rules\AlphaNumeric;
use Tempest\Validation\Rules\Between;
use function Tempest\view;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class ViewComponentTest extends FrameworkIntegrationTestCase
{
    #[DataProvider('view_components')]
    public function test_view_components(string $component, string $rendered): void
    {
        $this->assertSame(
            expected: $rendered,
            actual: $this->render(view($component)),
        );
    }

    public function test_view_component_with_php_code_in_attribute(): void
    {
        $this->assertSame(
            expected: '<div foo="hello" bar="barValue"></div>',
            actual: $this->render(view(
                <<<'HTML'
            <x-my :foo="$this->input" bar="barValue"></x-my>
            HTML,
            )->data(input: 'hello')),
        );
    }

    public function test_view_component_with_php_code_in_slot(): void
    {
        $this->assertSame(
            expected: '<div>bar</div>',
            actual: $this->render(view('<x-my>{{ $this->foo }}</x-my>')->data(foo: 'bar')),
        );
    }

    public function test_nested_components(): void
    {
        $this->assertSame(
            expected: <<<'HTML'
            <form action="#" method="post">
                <div> <div>
                <label for="a">a</label>
                <input type="number" name="a" id="a" value="" />
                
            </div> </div> <div>
                <label for="b">b</label>
                <input type="text" name="b" id="b" value="" />
                
            </div>
            </form>
            HTML,
            actual: $this->render(view(
                <<<'HTML'
            <x-form action="#">
                <div>
                    <x-input name="a" label="a" type="number"></x-input>
                </div>
                <x-input name="b" label="b" type="text" />
            </x-form>
            HTML,
            )),
        );
    }

    public function test_view_component_with_injected_view(): void
    {
        $between = new Between(min: 1, max: 10);
        $alphaNumeric = new AlphaNumeric();

        $this->container->get(Session::class)->flash(
            Session::VALIDATION_ERRORS,
            ['name' => [$between, $alphaNumeric]],
        );

        $this->container->get(Session::class)->flash(
            Session::ORIGINAL_VALUES,
            ['name' => 'original name'],
        );

        $html = $this->render(view(
            <<<'HTML'
            <x-input name="name" label="a" type="number" />
            HTML,
        ));

        $this->assertStringContainsString('value="original name"', $html);
        $this->assertStringContainsString($between->message(), $html);
        $this->assertStringContainsString($alphaNumeric->message(), $html);
    }

    public function test_component_with_injected_dependency(): void
    {
        $this->assertSame(
            expected: 'hi',
            actual: $this->render(view('<x-with-injection />')),
        );
    }

    public function test_component_with_if(): void
    {
        $this->assertSame(
            expected: '<div>true</div>',
            actual: $this->render(view('<x-my :if="$this->show">true</x-my><x-my :else>false</x-my>')->data(show: true)),
        );

        $this->assertSame(
            expected: '<div>false</div>',
            actual: $this->render(view('<x-my :if="$this->show">true</x-my><x-my :else>false</x-my>')->data(show: false)),
        );
    }

    public function test_component_with_foreach(): void
    {
        $this->assertSame(
            expected: '<div>a</div>
<div>b</div>',
            actual: $this->render(view('<x-my :foreach="$this->items as $foo">{{ $this->foo }}</x-my>')->data(items: ['a', 'b'])),
        );
    }

    public static function view_components(): Generator
    {
        yield [
            '<x-my></x-my>',
            '<div></div>',
        ];

        yield [
            '<x-my>body</x-my>',
            '<div>body</div>',
        ];

        yield [
            '<x-my><p>a</p><p>b</p></x-my>',
            '<div><p>a</p><p>b</p></div>',
        ];

        yield [
            '<x-my>body</x-my><x-my>body</x-my>',
            '<div>body</div><div>body</div>',
        ];

        yield [
            '<x-my foo="fooValue" bar="barValue">body</x-my>',
            '<div foo="fooValue" bar="barValue">body</div>',
        ];
    }

    public function test_anonymous_view_component(): void
    {
        $html = $this->render(view('<x-my-a>hi</x-my-a>'));

        lw($html);
    }
}
