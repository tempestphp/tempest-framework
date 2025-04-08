<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\View;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tempest\Core\AppConfig;
use Tempest\Core\Environment;
use Tempest\Router\Session\Session;
use Tempest\Validation\Rules\AlphaNumeric;
use Tempest\Validation\Rules\Between;
use Tempest\View\Exceptions\InvalidDataAttribute;
use Tempest\View\Exceptions\ViewVariableIsReserved;
use Tempest\View\ViewCache;
use Tests\Tempest\Fixtures\Views\Chapter;
use Tests\Tempest\Fixtures\Views\DocsView;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\view;

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

    public function test_view_can_access_dynamic_slots(): void
    {
        $this->registerViewComponent('x-test', <<<'HTML'
        <div :foreach="$slots as $slot">
            <div>{{ $slot->name }}</div>
            <div>{{ $slot->attributes['language'] }}</div>
            <div>{{ $slot->language }}</div>
            <div>{!! $slot->content !!}</div>
        </div>
        HTML);

        $html = $this->render(<<<'HTML_WRAP'
        <x-test>
            <x-slot name="slot-php" language="PHP">PHP Body</x-slot>
            <x-slot name="slot-html" language="HTML">HTML Body</x-slot>
        </x-test>
        HTML_WRAP);

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'HTML_WRAP'
        <div><div>slot-php</div><div>PHP</div><div>PHP</div><div>PHP Body</div></div>
        <div><div>slot-html</div><div>HTML</div><div>HTML</div><div>HTML Body</div></div>
        HTML_WRAP, $html);
    }

    public function test_dynamic_slots_are_cleaned_up(): void
    {
        $this->registerViewComponent('x-test', <<<'HTML'
        <div :foreach="$slots as $slot">
            <div>{{ $slot->name }}</div>
        </div>
        <x-slot />
        HTML);

        $html = $this->render(<<<'HTML'
        <x-test>
            <x-slot name="a"></x-slot>
            <x-slot name="b"></x-slot>
            <div :if="isset($slots)">internal slots still here</div>
            <div :else>internal slots are cleared</div>
        </x-test>

        <div :if="isset($slots)">slots still here</div>
        <div :else>slots are cleared</div>
        HTML);

        $this->assertStringContainsString('<div>internal slots still here</div>', $html);
        $this->assertStringContainsString('<div>slots are cleared</div>', $html);
    }

    public function test_slots_with_nested_view_components(): void
    {
        $this->registerViewComponent('x-a', <<<'HTML'
        <x-slot />
        <div :foreach="$slots as $slot">
            <div>A{{ $slot->name }}</div>
        </div>
        HTML);

        $this->registerViewComponent('x-b', <<<'HTML'
        <div :foreach="$slots as $slot">
            <div>B{{ $slot->name }}</div>
        </div>
        HTML);

        $html = $this->render(<<<'HTML'
        <x-a>
            <x-b>
                <x-slot name="1"></x-slot>
                <x-slot name="2"></x-slot>
            </x-b>

            <x-slot name="3"></x-slot>
            <x-slot name="4"></x-slot>
        </x-a>
        HTML);

        $this->assertStringContainsString('<div>B1</div>', $html);
        $this->assertStringContainsString('<div>B2</div>', $html);
        $this->assertStringContainsString('<div>A3</div>', $html);
        $this->assertStringContainsString('<div>A4</div>', $html);
    }

    public function test_slots_is_a_reserved_variable(): void
    {
        $this->expectException(ViewVariableIsReserved::class);
        $this->expectExceptionMessage('Cannot use reserved variable name `slots`');

        $this->render('', slots: []);
    }

    public function test_nested_components(): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
            expected: <<<'HTML'
            <form action="#" method="post"><div><div><label for="a">a</label><input type="number" name="a" id="a" value></div></div><div><label for="b">b</label><input type="text" name="b" id="b" value></div></form>
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

    public function test_component_with_anther_component_included(): void
    {
        $html = $this->render('<x-view-component-with-another-one-included-a/>');

        $this->assertStringContainsStringIgnoringLineEndings(<<<'HTML'
        hi

            
        <div class="slot-b"><div class="slot-a"></div></div>
        HTML, $html);
    }

    public function test_component_with_anther_component_included_with_slot(): void
    {
        $html = $this->render('<x-view-component-with-another-one-included-a>test</x-view-component-with-another-one-included-a>');

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'HTML'
        hi

            
        <div class="slot-b"><div class="slot-a">
                    test
                </div></div>
        HTML, $html);
    }

    public function test_view_component_with_injected_view(): void
    {
        $between = new Between(min: 1, max: 10);
        $alphaNumeric = new AlphaNumeric();

        $session = $this->container->get(Session::class);

        $session->flash(
            Session::VALIDATION_ERRORS,
            ['name' => [$between, $alphaNumeric]],
        );

        $session->flash(
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
            $this->render(view('<x-my-a>hi</x-my-a>')),
        );
    }

    public function test_with_header(): void
    {
        $this->assertSame(
            '/',
            $this->render(view('<x-with-header></x-with-header>')),
        );
    }

    public function test_with_passed_variable(): void
    {
        $rendered = $this->render(
            view('<x-with-variable :variable="$variable"></x-with-variable>')->data(
                variable: 'test',
            ),
        );

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<HTML
            <div>test</div>
            HTML,
            $rendered,
        );
    }

    public function test_with_passed_data(): void
    {
        $rendered = $this->render(
            view('<x-with-variable variable="test"></x-with-variable>'),
        );

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<HTML
            <div>test</div>
            HTML,
            $rendered,
        );
    }

    public function test_with_passed_php_data(): void
    {
        $rendered = $this->render(
            view(<<<HTML
            <x-with-variable :variable="strtoupper('test')"></x-with-variable>
            HTML),
        );

        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<HTML
            <div>TEST</div>
            HTML,
            $rendered,
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

        $this->assertStringContainsString('a', $rendered);
        $this->assertStringContainsString('b', $rendered);
        $this->assertStringContainsString('c', $rendered);
        $this->assertStringCount($rendered, '<div>', 3);
        $this->assertStringCount($rendered, '</div>', 3);
    }

    public function test_inline_view_variables_passed_to_component(): void
    {
        $html = $this->render(view(__DIR__ . '/../../Fixtures/Views/view-defined-local-vars-b.view.php'));

        $this->assertStringContainsString('fromPHP', $html);
        $this->assertStringContainsString('fromString', $html);
        $this->assertStringContainsString('nothing', $html);
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

        $this->assertStringCount($html, 'test', 2);
    }

    public function test_php_code_in_attribute(): void
    {
        $this->expectException(InvalidDataAttribute::class);

        $html = $this->render(view(__DIR__ . '/../../Fixtures/Views/button-usage.view.php'));
    }

    public function test_template_component(): void
    {
        $html = $this->render(
            <<<'HTML'
                <x-template :foreach="$items as $item">
                    <div>item {{ $item }}</div>
                    <div>boo</div>
                </x-template>
            HTML,
            items: ['a', 'b', 'c'],
        );

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'HTML'
        <div>item a</div><div>boo</div>
        <div>item b</div><div>boo</div>
        <div>item c</div><div>boo</div>
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
            '<div><p>a</p><p>b</p></div>',
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

    public function test_full_html_document_as_component(): void
    {
        $this->registerViewComponent('x-layout', <<<'HTML'
            <html lang="en">
            <head>
                <title>Tempest View</title>
            </head>
            <body>
                <x-slot />
            </body>
            </html>
        HTML);

        $html = $this->render(<<<'HTML'
        <x-layout>
            Hello World
        </x-layout>
        HTML);

        $this->assertStringContainsString('<html lang="en"><head><title>Tempest View</title></head><body>', $html);
        $this->assertStringContainsString('Hello World', $html);
        $this->assertStringContainsString('</body></html>', $html);
    }

    public function test_empty_slots_are_commented_out(): void
    {
        $this->registerViewComponent('x-layout', <<<'HTML'
        <html lang="en">
        <head>
            <x-slot name="styles" />
            <link rel="stylesheet" href="#" />
        </head>
        <body></body>
        </html>
        HTML);

        $html = $this->render(<<<'HTML'
        <x-layout>
        </x-layout>
        HTML);

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'HTML'
        <html lang="en"><head><!--<x-slot name="styles" />--><link rel="stylesheet" href="#"></head><body></body></html>
        HTML, $html);
    }

    public function test_empty_slots_are_removed_in_production(): void
    {
        $this->container->get(AppConfig::class)->environment = Environment::PRODUCTION;

        $this->registerViewComponent('x-layout', <<<'HTML'
        <html lang="en">
        <head>
            <x-slot name="styles" />
            <link rel="stylesheet" href="#" />
        </head>
        <body></body>
        </html>
        HTML);

        $html = $this->render(<<<'HTML'
        <x-layout>
        </x-layout>
        HTML);

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'HTML'
        <html lang="en"><head><link rel="stylesheet" href="#"></head><body></body></html>
        HTML, $html);
    }

    public function test_custom_components_in_head(): void
    {
        $this->registerViewComponent('x-custom-link', <<<'HTML'
        <link rel="stylesheet" href="#" />
        HTML);

        $html = $this->render(<<<'HTML'
        <html lang="en">
        <head>
            <x-custom-link />
        </head>
        <body class="a"></body>
        </html>
        HTML);

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'HTML'
        <html lang="en"><head><link rel="stylesheet" href="#">
        </head><body class="a"></body></html>
        HTML, $html);
    }

    public function test_head_injection(): void
    {
        $this->registerViewComponent('x-custom-link', <<<'HTML'
        <link rel="stylesheet" href="#" />
        HTML);

        $html = $this->render(<<<'HTML'
        <!doctype html>
        <html lang="en">
        <head>
            <title>Foo</title>
            <meta charset="utf-8" />
            <x-custom-link />
            <meta name="description" content="bar" />
        </head>
        <body class="a">b</body>
        </html>
        HTML);

        $this->assertSnippetsMatch(<<<'HTML'
        <!doctype html>
        <html lang="en"><head><title>Foo</title><meta charset="utf-8"><link rel="stylesheet" href="#">
        <meta name="description" content="bar"></head><body class="a">b
        </body></html>
        HTML, $html);
    }

    public function test_attributes_variable_in_view_component(): void
    {
        $this->registerViewComponent('x-test', <<<'HTML'
        <div x-data="foo {{ $attributes['x-data'] }}"></div>
        HTML);

        $html = $this->render(<<<'HTML'
        <x-test x-data="bar"></x-test>
        HTML);

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'HTML'
        <div x-data="foo bar"></div>
        HTML, $html);
    }

    public function test_fallthrough_attributes(): void
    {
        $this->registerViewComponent('x-test', <<<'HTML'
        <div></div>
        HTML);

        $html = $this->render(<<<'HTML'
        <x-test class="test" style="text-decoration: underline;" id="test"></x-test>
        HTML);

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'HTML'
        <div class="test" style="text-decoration: underline;" id="test"></div>
        HTML, $html);
    }

    public function test_merged_fallthrough_attributes(): void
    {
        $this->registerViewComponent('x-test', <<<'HTML'
        <div class="foo" style="font-weight: bold;" id="other"></div>
        HTML);

        $html = $this->render(<<<'HTML'
        <x-test class="test" style="text-decoration: underline;" id="test"></x-test>
        HTML);

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'HTML'
        <div class="foo test" style="font-weight: bold; text-decoration: underline;" id="test"></div>
        HTML, $html);
    }

    public function test_file_name_component(): void
    {
        $html = $this->render('<x-file-component></x-file-component>');

        $this->assertSame('<div>hi!</div>', $html);
    }

    public function test_array_attribute(): void
    {
        $html = $this->render(<<<'HTML'
        <div :x="['foo', 'bar']"></div>
        HTML);

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'HTML'
        <div x="foo bar"></div>
        HTML, $html);
    }

    public function test_merge_class(): void
    {
        $this->registerViewComponent('x-test', <<<'HTML'
        <div class="inner" :class="'upper'"></div>
        HTML);

        $html = $this->render(<<<'HTML'
        <x-test></x-test>
        HTML);

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'HTML'
        <div class="inner upper"></div>
        HTML, $html);
    }

    public function test_attribute_precedence(): void
    {
        $this->markTestSkipped('TODO');

        // Order should be: upperB > upperA > innerB > innerA
        //        $this->registerViewComponent('x-test', <<<'HTML'
        //        <div data-foo="innerA" :data-foo="'innerB'"></div>
        //        HTML);
        //        $html = $this->render(<<<'HTML'
        //        <x-test data-foo="upperA" :data-foo="'upperB'"></x-test>
        //        HTML);
        //        $this->assertStringEqualsStringIgnoringLineEndings('<div data-foo="upperB"></div>', $html);
    }

    public function test_does_not_duplicate_br(): void
    {
        $this->registerViewComponent('x-html-base', <<<'HTML'
            <!doctype html>
            <html lang="en">
                <head>
                </head>
                <body>
                    <x-slot />
                </body>
            </html>
        HTML);

        $html = $this->render(<<<'HTML'
            <x-html-base>
                <br />
                <hr />
            </x-html-base>
        HTML);

        $this->assertSnippetsMatch(<<<'HTML'
        <!doctype html>
        <html lang="en"><head></head><body><br><hr></body></html>
        HTML, $html);
    }

    public function test_renders_minified_html_with_void_elements(): void
    {
        $html = $this->render(<<<'HTML'
            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" view-box="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M7 9.667A2.667 2.667 0 0 1 9.667 7h8.666A2.667 2.667 0 0 1 21 9.667v8.666A2.667 2.667 0 0 1 18.333 21H9.667A2.667 2.667 0 0 1 7 18.333z"/><path d="M4.012 16.737A2 2 0 0 1 3 15V5c0-1.1.9-2 2-2h10c.75 0 1.158.385 1.5 1"/></g></svg>
        HTML);

        $this->assertSnippetsMatch(<<<'HTML'
        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" view-box="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M7 9.667A2.667 2.667 0 0 1 9.667 7h8.666A2.667 2.667 0 0 1 21 9.667v8.666A2.667 2.667 0 0 1 18.333 21H9.667A2.667 2.667 0 0 1 7 18.333z"></path><path d="M4.012 16.737A2 2 0 0 1 3 15V5c0-1.1.9-2 2-2h10c.75 0 1.158.385 1.5 1"></path></g></svg>
        HTML, $html);
    }

    public function test_multiple_instances_of_custom_component_using_slots(): void
    {
        $this->registerViewComponent('x-foo-bar', 'FOO-BAR');

        $this->registerViewComponent('x-test', <<<'HTML'
        <div>
            <x-foo-bar />
            <x-slot name="test" />
        </div>
        HTML);

        $html = $this->render(<<<'HTML'
        <x-test>
            <x-slot name="test">
                <x-foo-bar />
            </x-slot>
        </x-test>
        HTML);

        $this->assertSnippetsMatch(<<<'HTML'
        <div>FOO-BAR
        FOO-BAR
        </div>
        HTML, $html);
    }

    private function assertSnippetsMatch(string $expected, string $actual): void
    {
        $expected = str_replace([PHP_EOL, ' '], '', $expected);
        $actual = str_replace([PHP_EOL, ' '], '', $actual);

        $this->assertSame($expected, $actual);
    }
}
