<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\View;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tempest\Core\Environment;
use Tempest\Http\Session\Session;
use Tempest\Validation\Rules\IsAlphaNumeric;
use Tempest\Validation\Rules\IsBetween;
use Tempest\Validation\Validator;
use Tempest\View\Exceptions\DataAttributeWasInvalid;
use Tempest\View\Exceptions\ViewVariableWasReserved;
use Tempest\View\ViewCache;
use Tests\Tempest\Fixtures\Views\Chapter;
use Tests\Tempest\Fixtures\Views\DocsView;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\View\view;

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
        $this->assertSnippetsMatch(
            expected: $rendered,
            actual: $this->render(view($component)),
        );
    }

    public function test_view_component_with_php_code_in_attribute(): void
    {
        $this->registerViewComponent('x-test', '<div :foo="$foo" :bar="$bar"></div>');

        $this->assertSame(
            expected: '<div foo="hello" bar="barValue"></div>',
            actual: $this->render(
                <<<'HTML'
                <x-test :foo="$input" bar="barValue"></x-test>
                HTML,
                input: 'hello',
            ),
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
        <div :foreach="$slots as $slot" :if="$slot->name !== 'default'">
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

        $this->assertSnippetsMatch(<<<'HTML_WRAP'
        <div><div>slot-php</div><div>PHP</div><div>PHP</div><div>PHP Body</div></div>
        <div><div>slot-html</div><div>HTML</div><div>HTML</div><div>HTML Body</div></div>
        HTML_WRAP, $html);
    }

    public function test_dynamic_slots_are_cleaned_up(): void
    {
        $this->registerViewComponent('x-test', <<<'HTML'
        <div :foreach="$slots as $slot" :if="$slot->name !== 'default'">
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

    public function test_dynamic_slots_include_the_default_slot(): void
    {
        $this->registerViewComponent('x-test', <<<'HTML'
        <div>{{ $slots['default']->name }}</div>
        <div>{{ $slots['default']->content }}</div>
        HTML);

        $html = $this->render('<x-test>Hello</x-test>');

        $this->assertSnippetsMatch(
            <<<'HTML'
            <div>default</div>
            <div>Hello</div>
            HTML,
            $html,
        );
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
        $this->expectException(ViewVariableWasReserved::class);
        $this->expectExceptionMessage('Cannot use reserved variable name `slots`');

        $this->render('', slots: []);
    }

    public function test_scope_does_not_leak_data(): void
    {
        $html = $this->render(<<<'HTML'
        <x-input name="a" />
        <x-input name="b" />
        HTML);

        $this->assertStringContainsString('<label for="a">A</label>', $html);
        $this->assertStringContainsString('<input type="text" name="a" id="a"', $html);

        $this->assertStringContainsString('<label for="b">B</label>', $html);
        $this->assertStringContainsString('<input type="text" name="b" id="b"', $html);
    }

    public function test_component_with_anther_component_included(): void
    {
        $html = $this->render('<x-view-component-with-another-one-included-a/>');

        $this->assertSnippetsMatch(<<<'HTML'
        hi

            
        <div class="slot-b"><div class="slot-a"></div></div>
        HTML, $html);
    }

    public function test_component_with_anther_component_included_with_slot(): void
    {
        $html = $this->render('<x-view-component-with-another-one-included-a>test</x-view-component-with-another-one-included-a>');

        $this->assertSnippetsMatch(<<<'HTML'
        hi

            
        <div class="slot-b"><div class="slot-a">
                    test
                </div></div>
        HTML, $html);
    }

    public function test_view_component_with_injected_view(): void
    {
        $between = new IsBetween(min: 1, max: 10);
        $alphaNumeric = new IsAlphaNumeric();

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

        $validator = $this->container->get(Validator::class);

        $this->assertStringContainsString('value="original name"', $html);
        $this->assertStringContainsString($validator->getErrorMessage($between), $html);
        $this->assertStringContainsString($validator->getErrorMessage($alphaNumeric), $html);
    }

    public function test_component_with_if(): void
    {
        $this->assertSame(
            expected: '<div>true</div>',
            actual: $this->render('<x-my :if="$show">true</x-my><x-my :else>false</x-my>', show: true),
        );

        $this->assertSame(
            expected: '<div>false</div>',
            actual: $this->render('<x-my :if="$show">true</x-my><x-my :else>false</x-my>', show: false),
        );
    }

    public function test_component_with_foreach(): void
    {
        $this->registerViewComponent('x-test', <<<'HTML'
        <div><x-slot /></div>
        HTML);

        $this->assertSnippetsMatch(
            expected: '<div>a</div><div>b</div>',
            actual: $this->render('<x-test :foreach="$items as $foo">{{ $foo }}</x-test>', items: ['a', 'b']),
        );
    }

    public function test_anonymous_view_component(): void
    {
        $this->assertSame(
            <<<HTML
            <div class="anonymous">hi</div>
            HTML,
            $this->render('<x-my-a>hi</x-my-a>'),
        );
    }

    public function test_with_header(): void
    {
        $this->assertSame(
            '/',
            $this->render('<x-with-header></x-with-header>'),
        );
    }

    public function test_with_passed_variable(): void
    {
        $rendered = $this->render(
            view('<x-with-variable :variable="$variable"></x-with-variable>')->data(
                variable: 'test',
            ),
        );

        $this->assertSnippetsMatch(
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

        $this->assertSnippetsMatch(
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

        $this->assertSnippetsMatch(
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
            <<<'HTML'
            <x-with-variable :foreach="$this->variables as $variable" />
            HTML,
            variables: ['a', 'b', 'c'],
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

        $this->assertSnippetsMatch(<<<HTML
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
        $this->registerViewComponent('x-test', <<<'HTML'
            {{ $metaType ?? 'nothing' }}
        HTML);

        $this->assertSame('test', $this->render('<x-test meta_type="test">'));
        $this->assertSame('test', $this->render('<x-test meta-type="test">'));
        $this->assertSame('test', $this->render('<x-test metaType="test">'));
    }

    public function test_php_code_in_attribute(): void
    {
        $this->expectException(DataAttributeWasInvalid::class);

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

        $this->assertSnippetsMatch(<<<'HTML'
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
        $this->container->singleton(Environment::class, Environment::LOCAL);

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

        $this->assertSnippetsMatch(<<<'HTML'
        <html lang="en"><head><!--<x-slot name="styles" />--><link rel="stylesheet" href="#"></head><body></body></html>
        HTML, $html);
    }

    public function test_empty_slots_are_removed_in_production(): void
    {
        $this->container->singleton(Environment::class, Environment::PRODUCTION);

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

        $this->assertSnippetsMatch(<<<'HTML'
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

        $this->assertSnippetsMatch(<<<'HTML'
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

        $this->assertSnippetsMatch(<<<'HTML'
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

        $this->assertSnippetsMatch(<<<'HTML'
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

        $this->assertSnippetsMatch(<<<'HTML'
        <div class="foo test" style="font-weight: bold; text-decoration: underline;" id="test"></div>
        HTML, $html);
    }

    public function test_fallthrough_attributes_with_other_attributes(): void
    {
        $this->registerViewComponent('x-test', <<<'HTML'
        <div class="foo" style="font-weight: bold;" id="other"></div>
        HTML);

        $html = $this->render(<<<'HTML'
        <x-test class="test" style="text-decoration: underline;" id="test"></x-test>
        HTML);

        $this->assertSnippetsMatch(<<<'HTML'
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

        $this->assertSnippetsMatch(<<<'HTML'
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

        $this->assertSnippetsMatch(<<<'HTML'
        <div class="inner upper"></div>
        HTML, $html);
    }

    public function test_merge_class_from_template_to_component(): void
    {
        $this->registerViewComponent('x-test', <<<'HTML'
        <div class="bg-gray-200"></div>
        HTML);

        $html = $this->render(<<<'HTML'
        <x-test class="bg-red-500"></x-test>
        HTML);

        $this->assertSnippetsMatch(<<<'HTML'
        <div class="bg-gray-200 bg-red-500"></div>
        HTML, $html);
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

    public function test_slots_with_hyphens(): void
    {
        $this->registerViewComponent('x-test', <<<'HTML'
        <div>
            <x-slot name="test-slot" />
        </div>
        HTML);

        $html = $this->render(<<<'HTML'
        <x-test>
            <x-slot name="test-slot">
                Hi
            </x-slot>
        </x-test>
        HTML);

        $this->assertSnippetsMatch(<<<'HTML'
            <div>
                    Hi
            </div>
        HTML, $html);
    }

    public function test_nested_table_components(): void
    {
        $this->registerViewComponent('x-my-table-thead', '<thead><x-slot/></thead>');
        $this->registerViewComponent('x-my-table-tbody', '<tbody><x-slot/></tbody>');
        $this->registerViewComponent('x-my-table-tr', '<tr><x-slot/></tr>');
        $this->registerViewComponent('x-my-table-td', '<td><x-slot/></td>');
        $this->registerViewComponent('x-my-table-th', '<th><x-slot/></th>');

        $html = $this->render(<<<'HTML'
        <table>
            <x-my-table-thead>
                <x-my-table-tr>
                    <x-my-table-th>Header 1</x-my-table-th>
                </x-my-table-tr>
            </x-my-table-thead>
            <x-my-table-tbody>
                <x-my-table-tr>
                    <x-my-table-td>Row 1, Cell 1</x-my-table-td>
                </x-my-table-tr>
            </x-my-table-tbody>
        </table>
        HTML);

        $this->assertSnippetsMatch(<<<'HTML'
            <table>
                <thead>
                    <tr>
                        <th>Header1</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Row 1, Cell 1</td>
                    </tr>
                </tbody>
            </table>
        HTML, $html);
    }

    public function test_dynamic_view_component_with_string_name(): void
    {
        $this->registerViewComponent('x-test', '<div>{{ $prop }}</div>');

        $html = $this->render(<<<'HTML'
        <x-component is="x-test" prop="test"/>
        HTML);

        $this->assertSame('<div>test</div>', $html);
    }

    public function test_dynamic_view_component_with_expression_name(): void
    {
        $this->registerViewComponent('x-test', '<div>{{ $prop }}</div>');

        $html = $this->render(<<<'HTML'
        <x-component :is="$name" prop="test" />
        HTML, name: 'x-test');

        $this->assertSame('<div>test</div>', $html);
    }

    public function test_dynamic_view_component_with_variable_attribute(): void
    {
        $this->registerViewComponent('x-test', '<div>{{ $prop }}</div>');

        $html = $this->render(<<<'HTML'
        <x-component :is="$name" :prop="$input" t="t" />
        HTML, name: 'x-test', input: 'test');

        $this->assertSame('<div>test</div>', $html);
    }

    public function test_dynamic_view_component_with_slot(): void
    {
        $this->registerViewComponent('x-test', '<div><x-slot/></div>');

        $html = $this->render(<<<'HTML'
        <x-component :is="$name">test</x-component>
        HTML, name: 'x-test');

        $this->assertSnippetsMatch('<div>test</div>', $html);
    }

    public function test_nested_slots(): void
    {
        $this->registerViewComponent('x-a', '<a><x-slot /></a>');
        $this->registerViewComponent('x-b', '<x-a><b><x-slot /></b></x-a>');

        $html = $this->render(<<<'HTML'
        <x-b>
            hi
        </x-b>
        HTML);

        $this->assertSnippetsMatch('<a><b>hi</b></a>', $html);
    }

    public function test_nested_slots_with_escaping(): void
    {
        $this->registerViewComponent('x-a', '<a><x-slot /></a>');
        $this->registerViewComponent('x-b', <<<'HTML'
        <?php 
        use function \Tempest\get;
        use \Tempest\Core\Environment;
        ?>
        {{ get(Environment::class)->value }}
        HTML);

        $html = $this->render(<<<'HTML'
        <x-a>
            <x-b />
        </x-a>
        HTML);

        $this->assertSnippetsMatch('<a>testing</a>', $html);
    }

    public function test_repeated_local_var_across_view_components(): void
    {
        $this->registerViewComponent('x-test', '<div :thing="$thing">{{ $thing }}</div>');

        $html = $this->render(<<<'HTML'
        <x-test thing="a" />
        <x-test thing="b" />
        <x-test thing="c" />
        HTML);

        $this->assertSnippetsMatch('
            <div thing="a">a</div>
            <div thing="b">b</div>
            <div thing="c">c</div>
        ', $html);
    }

    public function test_escape_expression_attribute_in_view_components(): void
    {
        $this->registerViewComponent('x-test', '<div class="foo" ::escaped="foo"></div>');

        $html = $this->render('<x-test/>');

        $this->assertSnippetsMatch('<div class="foo" :escaped="foo"></div>', $html);
    }

    public function test_default_slot_value(): void
    {
        $this->registerViewComponent('x-test', <<<'HTML'
        <x-slot>Default</x-slot>
        <x-slot name="a">Default A</x-slot>
        <x-slot name="b">Default B</x-slot>
        HTML);

        $this->assertSnippetsMatch(
            <<<'HTML'
            Overwritten
            Overwritten A
            Overwritten B
            HTML,
            $this->render('<x-test>
        Overwritten
        <x-slot name="a">Overwritten A</x-slot>
        <x-slot name="b">Overwritten B</x-slot>
        </x-test>'),
        );

        $this->assertSnippetsMatch(
            <<<'HTML'
            Overwritten
            Default A
            Overwritten B
            HTML,
            $this->render('<x-test>
        Overwritten
        <x-slot name="b">Overwritten B</x-slot>
        </x-test>'),
        );

        $this->assertSnippetsMatch(<<<'HTML'
        Default
        Default A
        Default B
        HTML, $this->render('<x-test></x-test>'));
    }

    public function test_view_variables_are_passed_into_the_component(): void
    {
        $this->registerViewComponent('x-a', '<x-slot />');

        $html = $this->render(<<<'HTML'
        <x-a>
        {{ $title }}
        </x-a>
        HTML, title: 'test');

        $this->assertSnippetsMatch('test', $html);
    }

    public function test_capture_outer_scope_view_component_variables(): void
    {
        $this->registerViewComponent('x-a', '<x-slot />');
        $this->registerViewComponent('x-b', '<x-slot />');

        $html = $this->render(
            <<<'HTML'
            <x-a :foreach="$items as $item">
                <x-b> 
                    {{ $item }}
                </x-b>
            </x-a>
            HTML,
            items: ['a', 'b'],
        );

        $this->assertSnippetsMatch('a b', $html);
    }

    public function test_fallthrough_attribute_without_value(): void
    {
        $this->registerViewComponent('x-test', '<div :if="$flag">hi</div>');

        $this->assertSnippetsMatch('', $this->render('<x-test />'));
        $this->assertSnippetsMatch('<div>hi</div>', $this->render('<x-test :flag/>'));
    }
}
