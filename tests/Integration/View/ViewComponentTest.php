<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\View;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tempest\Http\Session\Session;
use Tempest\Validation\Rules\AlphaNumeric;
use Tempest\Validation\Rules\Between;
use function Tempest\view;
use Tempest\View\ViewCache;
use Tests\Tempest\Fixtures\Views\Chapter;
use Tests\Tempest\Fixtures\Views\DocsView;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class ViewComponentTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->container->get(ViewCache::class)->clear();
    }

    #[DataProvider('view_components')]
    public function test_view_components(string $component, string $rendered): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
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
        $this->assertStringEqualsStringIgnoringLineEndings(
            expected: str_replace(PHP_EOL, '', <<<'HTML'
            <form action="#" method="post" >
                <div><div>
                <label for="a">a</label>
                <input type="number" name="a" id="a" value="" />
                
            </div></div>
            <div>
                <label for="b">b</label>
                <input type="text" name="b" id="b" value="" />
                
            </div>
            </form>
            HTML),
            actual: str_replace(PHP_EOL, '', $this->render(view(
                <<<'HTML'
            <x-form action="#">
                <div>
                    <x-input name="a" label="a" type="number"></x-input>
                </div>
                <x-input name="b" label="b" type="text" />
            </x-form>
            HTML,
            ))),
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
            actual: $this->render('<x-with-injection />'),
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
        $this->assertStringEqualsStringIgnoringLineEndings(
            expected: '<div>a</div>
<div>b</div>',
            actual: $this->render(view('<x-my :foreach="$this->items as $foo">{{ $foo }}</x-my>')->data(items: ['a', 'b'])),
        );
    }

    public function test_anonymous_view_component(): void
    {
        $this->assertSame(
            <<<HTML
            <div class="anonymous">hi</div>
            HTML,
            $this->render(view('<x-my-a>hi</x-my-a>'))
        );
    }

    public function test_with_header(): void
    {
        $this->assertSame(
            '/',
            $this->render(view('<x-with-header></x-with-header>'))
        );
    }

    public function test_with_passed_variable(): void
    {
        $rendered = $this->render(
            view('<x-with-variable :variable="$variable"></x-with-variable>')->data(
                variable: 'test'
            )
        );

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<HTML
        <div>
                test    </div>
        HTML,
            $rendered
        );
    }

    public function test_with_passed_data(): void
    {
        $rendered = $this->render(
            view('<x-with-variable variable="test"></x-with-variable>')
        );

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<HTML
        <div>
                test    </div>
        HTML,
            $rendered
        );
    }

    public function test_with_passed_php_data(): void
    {
        $rendered = $this->render(
            view(<<<HTML
            <x-with-variable :variable="strtoupper('test')"></x-with-variable>
            HTML)
        );

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<HTML
        <div>
                TEST    </div>
        HTML,
            $rendered
        );
    }

    public function test_view_component_with_nested_property_to_view(): void
    {
        $view = new DocsView(new Chapter('Current Title'));

        $html = $this->render($view);

        $this->assertStringContainsString('Current Title', $html);
    }

    public function test_view_component_with_nested_call_to_view(): void
    {
        $view = new DocsView(new Chapter('Current Title'));

        $html = $this->render($view);

        $this->assertStringContainsString('Next Title', $html);
    }

    public function test_with_passed_variable_within_loop(): void
    {
        $rendered = $this->render(
            view(
                <<<'HTML'
            <x-with-variable :foreach="$this->variables as $variable" :variable="$variable"></x-with-variable>
            HTML,
            )->data(
                variables: ['a', 'b', 'c'],
            ),
        );

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<HTML
        <div>        a    </div>    <div>        b    </div>    <div>        c    </div>
        HTML,
            str_replace(PHP_EOL, '', $rendered)
        );
    }

    public function test_inline_view_variables_passed_to_component(): void
    {
        $html = $this->render(view(__DIR__ . '/../../Fixtures/Views/view-defined-local-vars-b.view.php'));

        $this->assertSame(<<<HTML
        fromPHP
        
        
            fromString
        
        
            nothing
        HTML, $html);
    }

    public function test_view_component_attribute_variables_without_this(): void
    {
        $html = $this->render(view(__DIR__ . '/../../Fixtures/Views/view-component-attribute-without-this-b.view.php'));

        $this->assertSame(<<<HTML
        fromString
        HTML, $html);
    }

    public function test_view_component_slots_without_self_closing_tags(): void
    {
        $html = $this->render(view(__DIR__ . '/../../Fixtures/Views/view-component-with-non-self-closing-slot-b.view.php'));

        $this->assertStringEqualsStringIgnoringLineEndings(<<<HTML
        A: other slot
            B: other slot
            C: other slot
        
            A: 
            main slot
            
            B: 
            main slot
            
            C: 
            main slot
        HTML, $html);
    }

    public function test_view_component_with_camelcase_attribute(): void
    {
        $html = $this->render(view(__DIR__ . '/../../Fixtures/Views/view-component-with-camelcase-attribute-b.view.php'));

        $this->assertSame(<<<HTML
        test
        
        
            test
        HTML, $html);
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
            '<div><p>a</p>
<p>b</p></div>',
        ];

        yield [
            '<div>body</div>
<div>body</div>',
            '<div>body</div>
<div>body</div>',
        ];

        yield [
            '<x-my foo="fooValue" bar="barValue">body</x-my>',
            '<div foo="fooValue" bar="barValue">body</div>',
        ];
    }
}
