<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\View;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Core\Environment;
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Http\Session\FormSession;
use Tempest\Router\Exceptions\DevelopmentException;
use Tempest\Router\Exceptions\HtmlExceptionRenderer;
use Tempest\Validation\FailingRule;
use Tempest\Validation\Rules\IsAlphaNumeric;
use Tempest\Validation\Rules\IsBetween;
use Tempest\Validation\Validator;
use Tempest\View\Exceptions\DataAttributeWasInvalid;
use Tempest\View\Exceptions\ViewCompilationFailed;
use Tempest\View\Exceptions\ViewVariableWasReserved;
use Tempest\View\GenericView;
use Tempest\View\Renderers\TempestViewRenderer;
use Tempest\View\ViewCache;
use Tempest\View\ViewConfig;
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
    #[Test]
    public function renders_view_components(string $component, string $rendered): void
    {
        $this->assertSnippetsMatch(
            expected: $rendered,
            actual: $this->view->render(view($component)),
        );
    }

    #[Test]
    public function view_component_with_php_code_in_attribute(): void
    {
        $this->view->registerViewComponent('x-test', '<div :foo="$foo" :bar="$bar"></div>');

        $this->assertSame(
            expected: '<div foo="hello" bar="barValue"></div>',
            actual: $this->view->render(
                <<<'HTML'
                <x-test :foo="$input" bar="barValue"></x-test>
                HTML,
                input: 'hello',
            ),
        );
    }

    #[Test]
    public function view_component_with_php_code_in_slot(): void
    {
        $this->assertSame(
            expected: '<div>bar</div>',
            actual: $this->view->render(view('<x-my>{{ $this->foo }}</x-my>')->data(foo: 'bar')),
        );
    }

    #[Test]
    public function view_can_access_dynamic_slots(): void
    {
        $this->view->registerViewComponent('x-test', <<<'HTML'
        <div :foreach="$slots as $slot" :if="$slot->name !== 'default'">
            <div>{{ $slot->name }}</div>
            <div>{{ $slot->attributes['language'] }}</div>
            <div>{{ $slot->language }}</div>
            <div>{!! $slot->content !!}</div>
        </div>
        HTML);

        $html = $this->view->render(<<<'HTML_WRAP'
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

    #[Test]
    public function dynamic_slots_are_cleaned_up(): void
    {
        $this->view->registerViewComponent('x-test', <<<'HTML'
        <div :foreach="$slots as $slot" :if="$slot->name !== 'default'">
            <div>{{ $slot->name }}</div>
        </div>
        <x-slot />
        HTML);

        $html = $this->view->render(<<<'HTML'
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

    #[Test]
    public function dynamic_slots_include_the_default_slot(): void
    {
        $this->view->registerViewComponent('x-test', <<<'HTML'
        <div>{{ $slots['default']->name }}</div>
        <div>{{ $slots['default']->content }}</div>
        HTML);

        $html = $this->view->render('<x-test>Hello</x-test>');

        $this->assertSnippetsMatch(
            <<<'HTML'
            <div>default</div>
            <div>Hello</div>
            HTML,
            $html,
        );
    }

    #[Test]
    public function slots_with_nested_view_components(): void
    {
        $this->view->registerViewComponent('x-a', <<<'HTML'
        <x-slot />
        <div :foreach="$slots as $slot">
            <div>A{{ $slot->name }}</div>
        </div>
        HTML);

        $this->view->registerViewComponent('x-b', <<<'HTML'
        <div :foreach="$slots as $slot">
            <div>B{{ $slot->name }}</div>
        </div>
        HTML);

        $html = $this->view->render(<<<'HTML'
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

    #[Test]
    public function slots_is_a_reserved_variable(): void
    {
        $this->expectException(ViewVariableWasReserved::class);
        $this->expectExceptionMessage('Cannot use reserved variable name `slots`');

        $this->view->render('', slots: []);
    }

    #[Test]
    public function scope_does_not_leak_data(): void
    {
        $html = $this->view->render(<<<'HTML'
        <x-input name="a" />
        <x-input name="b" />
        HTML);

        $this->assertStringContainsString('<label for="a">A</label>', $html);
        $this->assertStringContainsString('<input type="text" name="a" id="a"', $html);

        $this->assertStringContainsString('<label for="b">B</label>', $html);
        $this->assertStringContainsString('<input type="text" name="b" id="b"', $html);
    }

    #[Test]
    public function component_with_anther_component_included(): void
    {
        $html = $this->view->render('<x-view-component-with-another-one-included-a/>');

        $this->assertSnippetsMatch(<<<'HTML'
        hi

            
        <div class="slot-b"><div class="slot-a"></div></div>
        HTML, $html);
    }

    #[Test]
    public function component_with_anther_component_included_with_slot(): void
    {
        $html = $this->view->render('<x-view-component-with-another-one-included-a>test</x-view-component-with-another-one-included-a>');

        $this->assertSnippetsMatch(<<<'HTML'
        hi

            
        <div class="slot-b"><div class="slot-a">
                    test
                </div></div>
        HTML, $html);
    }

    #[Test]
    public function view_component_with_injected_view(): void
    {
        $between = new FailingRule(new IsBetween(min: 1, max: 10));
        $alphaNumeric = new FailingRule(new IsAlphaNumeric());

        $formSession = $this->container->get(FormSession::class);
        $formSession->setErrors(
            ['name' => [$between, $alphaNumeric]],
        );

        $formSession->setOriginalValues(
            ['name' => 'original name'],
        );

        $html = $this->view->render(view(
            <<<'HTML'
            <x-input name="name" label="a" type="number" />
            HTML,
        ));

        $validator = $this->container->get(Validator::class);

        $this->assertStringContainsString('value="original name"', $html);
        $this->assertStringContainsString($validator->getErrorMessage($between), $html);
        $this->assertStringContainsString($validator->getErrorMessage($alphaNumeric), $html);
    }

    #[Test]
    public function component_with_if(): void
    {
        $this->assertSame(
            expected: '<div>true</div>',
            actual: $this->view->render('<x-my :if="$show">true</x-my><x-my :else>false</x-my>', show: true),
        );

        $this->assertSame(
            expected: '<div>false</div>',
            actual: $this->view->render('<x-my :if="$show">true</x-my><x-my :else>false</x-my>', show: false),
        );
    }

    #[Test]
    public function component_with_foreach(): void
    {
        $this->view->registerViewComponent('x-test', <<<'HTML'
        <div><x-slot /></div>
        HTML);

        $this->assertSnippetsMatch(
            expected: '<div>a</div><div>b</div>',
            actual: $this->view->render('<x-test :foreach="$items as $foo">{{ $foo }}</x-test>', items: ['a', 'b']),
        );
    }

    #[Test]
    public function anonymous_view_component(): void
    {
        $this->assertSame(
            <<<HTML
            <div class="anonymous">hi</div>
            HTML,
            $this->view->render('<x-my-a>hi</x-my-a>'),
        );
    }

    #[Test]
    public function with_header(): void
    {
        $this->assertSame(
            '/',
            $this->view->render('<x-with-header></x-with-header>'),
        );
    }

    #[Test]
    public function with_passed_variable(): void
    {
        $rendered = $this->view->render(
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

    #[Test]
    public function with_passed_data(): void
    {
        $rendered = $this->view->render(
            view('<x-with-variable variable="test"></x-with-variable>'),
        );

        $this->assertSnippetsMatch(
            <<<HTML
            <div>test</div>
            HTML,
            $rendered,
        );
    }

    #[Test]
    public function with_passed_php_data(): void
    {
        $rendered = $this->view->render(
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

    #[Test]
    public function view_component_with_nested_property_to_view(): void
    {
        $view = new DocsView(new Chapter('Current Title'));

        $html = $this->view->render($view);

        $this->assertStringContainsString('Current Title', $html);
    }

    #[Test]
    public function view_component_with_nested_call_to_view(): void
    {
        $view = new DocsView(new Chapter('Current Title'));

        $html = $this->view->render($view);

        $this->assertStringContainsString('Next Title', $html);
    }

    #[Test]
    public function with_passed_variable_within_loop(): void
    {
        $rendered = $this->view->render(
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

    #[Test]
    public function inline_view_variables_passed_to_component(): void
    {
        $html = $this->view->render(view(__DIR__ . '/../../Fixtures/Views/view-defined-local-vars-b.view.php'));

        $this->assertStringContainsString('fromPHP', $html);
        $this->assertStringContainsString('fromString', $html);
        $this->assertStringContainsString('nothing', $html);
    }

    #[Test]
    public function view_component_attribute_variables_without_this(): void
    {
        $html = $this->view->render(view(__DIR__ . '/../../Fixtures/Views/view-component-attribute-without-this-b.view.php'));

        $this->assertSame(<<<HTML
        fromString
        HTML, $html);
    }

    #[Test]
    public function view_component_slots_without_self_closing_tags(): void
    {
        $html = $this->view->render(view(__DIR__ . '/../../Fixtures/Views/view-component-with-non-self-closing-slot-b.view.php'));

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

    #[Test]
    public function view_component_with_camelcase_attribute(): void
    {
        $this->view->registerViewComponent('x-test', <<<'HTML'
            {{ $metaType ?? 'nothing' }}
        HTML);

        $this->assertSame('test', $this->view->render('<x-test meta_type="test">'));
        $this->assertSame('test', $this->view->render('<x-test meta-type="test">'));
        $this->assertSame('test', $this->view->render('<x-test metaType="test">'));
    }

    #[Test]
    public function php_code_in_attribute(): void
    {
        $this->expectException(DataAttributeWasInvalid::class);

        $html = $this->view->render(view(__DIR__ . '/../../Fixtures/Views/button-usage.view.php'));
    }

    #[Test]
    public function template_component(): void
    {
        $html = $this->view->render(
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

    #[Test]
    public function full_html_document_as_component(): void
    {
        $this->view->registerViewComponent('x-layout', <<<'HTML'
        <html lang="en">
        <head>
            <title>Tempest View</title>
        </head>
        <body>
            <x-slot />
        </body>
        </html>
        HTML);

        $html = $this->view->render(<<<'HTML'
        <x-layout>
            Hello World
        </x-layout>
        HTML);

        $this->assertStringContainsString(<<<'HTML'
        <html lang="en">
        <head>
            <title>Tempest View</title>
        </head>
        <body>
        HTML, $html);
        $this->assertStringContainsString('Hello World', $html);
        $this->assertStringContainsString('</body>', $html);
        $this->assertStringContainsString('</html>', $html);
    }

    #[Test]
    public function empty_slots_are_commented_out(): void
    {
        $this->container->singleton(Environment::class, Environment::LOCAL);

        $this->view->registerViewComponent('x-layout', <<<'HTML'
        <html lang="en">
        <head>
            <x-slot name="styles" />
            <link rel="stylesheet" href="#" />
        </head>
        <body></body>
        </html>
        HTML);

        $html = $this->view->render(<<<'HTML'
        <x-layout>
        </x-layout>
        HTML);

        $this->assertSnippetsMatch(<<<'HTML'
        <html lang="en"><head><!--<x-slot name="styles" />--><link rel="stylesheet" href="#"></head><body></body></html>
        HTML, $html);
    }

    #[Test]
    public function empty_slots_are_removed_in_production(): void
    {
        $this->container->singleton(Environment::class, Environment::PRODUCTION);

        $this->view->registerViewComponent('x-layout', <<<'HTML'
        <html lang="en">
        <head>
            <x-slot name="styles" />
            <link rel="stylesheet" href="#" />
        </head>
        <body></body>
        </html>
        HTML);

        $html = $this->view->render(<<<'HTML'
        <x-layout>
        </x-layout>
        HTML);

        $this->assertSnippetsMatch(<<<'HTML'
        <html lang="en"><head><link rel="stylesheet" href="#"></head><body></body></html>
        HTML, $html);
    }

    #[Test]
    public function custom_components_in_head(): void
    {
        $this->view->registerViewComponent('x-custom-link', <<<'HTML'
        <link rel="stylesheet" href="#" />
        HTML);

        $html = $this->view->render(<<<'HTML'
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

    #[Test]
    public function head_injection(): void
    {
        $this->view->registerViewComponent('x-custom-link', <<<'HTML'
        <link rel="stylesheet" href="#" />
        HTML);

        $html = $this->view->render(<<<'HTML'
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

    #[Test]
    public function attributes_variable_in_view_component(): void
    {
        $this->view->registerViewComponent('x-test', <<<'HTML'
        <div x-data="foo {{ $attributes['x-data'] }}"></div>
        HTML);

        $html = $this->view->render(<<<'HTML'
        <x-test x-data="bar"></x-test>
        HTML);

        $this->assertSnippetsMatch(<<<'HTML'
        <div x-data="foo bar"></div>
        HTML, $html);
    }

    #[Test]
    public function fallthrough_attributes(): void
    {
        // Root has no class/style/id — all three fall through from the call site.
        $this->view->registerViewComponent('x-test', <<<'HTML'
        <div></div>
        HTML);

        $html = $this->view->render(<<<'HTML'
        <x-test class="test" style="text-decoration: underline;" id="test"></x-test>
        HTML);

        $this->assertSnippetsMatch(<<<'HTML'
        <div class="test" style="text-decoration: underline;" id="test"></div>
        HTML, $html);
    }

    #[Test]
    public function merged_fallthrough_attributes(): void
    {
        // Root defines all three of class, style, and id. None of the parent values
        // are applied — the component's own declarations are left untouched.
        $this->view->registerViewComponent('x-test', <<<'HTML'
        <div class="foo" style="font-weight: bold;" id="other"></div>
        HTML);

        $html = $this->view->render(<<<'HTML'
        <x-test class="test" style="text-decoration: underline;" id="test"></x-test>
        HTML);

        $this->assertSnippetsMatch(<<<'HTML'
        <div class="foo" style="font-weight: bold;" id="other"></div>
        HTML, $html);
    }

    #[Test]
    public function fallthrough_attributes_with_other_attributes(): void
    {
        // Root defines all three of class, style, and id. None of the parent values
        // are applied — the component's own declarations are left untouched.
        // Use :apply inside the component template if you need to merge manually.
        $this->view->registerViewComponent('x-test', <<<'HTML'
        <div class="foo" style="font-weight: bold;" id="other"></div>
        HTML);

        $html = $this->view->render(<<<'HTML'
        <x-test class="test" style="text-decoration: underline;" id="test"></x-test>
        HTML);

        $this->assertSnippetsMatch(<<<'HTML'
        <div class="foo" style="font-weight: bold;" id="other"></div>
        HTML, $html);
    }

    #[Test]
    public function file_name_component(): void
    {
        $html = $this->view->render('<x-file-component></x-file-component>');

        $this->assertSame('<div>hi!</div>', $html);
    }

    #[Test]
    public function array_attribute(): void
    {
        $html = $this->view->render(<<<'HTML'
        <div :x="['foo', 'bar']"></div>
        HTML);

        $this->assertSnippetsMatch(<<<'HTML'
        <div x="foo bar"></div>
        HTML, $html);
    }

    #[Test]
    public function merge_class(): void
    {
        $this->view->registerViewComponent('x-test', <<<'HTML'
        <div class="inner" :class="'upper'"></div>
        HTML);

        $html = $this->view->render(<<<'HTML'
        <x-test></x-test>
        HTML);

        $this->assertSnippetsMatch(<<<'HTML'
        <div class="inner upper"></div>
        HTML, $html);
    }

    #[Test]
    public function does_not_duplicate_br(): void
    {
        $this->view->registerViewComponent('x-html-base', <<<'HTML'
            <!doctype html>
            <html lang="en">
                <head>
                </head>
                <body>
                    <x-slot />
                </body>
            </html>
        HTML);

        $html = $this->view->render(<<<'HTML'
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

    #[Test]
    public function renders_minified_html_with_void_elements(): void
    {
        $html = $this->view->render(<<<'HTML'
            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" view-box="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M7 9.667A2.667 2.667 0 0 1 9.667 7h8.666A2.667 2.667 0 0 1 21 9.667v8.666A2.667 2.667 0 0 1 18.333 21H9.667A2.667 2.667 0 0 1 7 18.333z"/><path d="M4.012 16.737A2 2 0 0 1 3 15V5c0-1.1.9-2 2-2h10c.75 0 1.158.385 1.5 1"/></g></svg>
        HTML);

        $this->assertSnippetsMatch(<<<'HTML'
        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" view-box="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M7 9.667A2.667 2.667 0 0 1 9.667 7h8.666A2.667 2.667 0 0 1 21 9.667v8.666A2.667 2.667 0 0 1 18.333 21H9.667A2.667 2.667 0 0 1 7 18.333z"></path><path d="M4.012 16.737A2 2 0 0 1 3 15V5c0-1.1.9-2 2-2h10c.75 0 1.158.385 1.5 1"></path></g></svg>
        HTML, $html);
    }

    #[Test]
    public function multiple_instances_of_custom_component_using_slots(): void
    {
        $this->view->registerViewComponent('x-foo-bar', 'FOO-BAR');

        $this->view->registerViewComponent('x-test', <<<'HTML'
        <div>
            <x-foo-bar />
            <x-slot name="test" />
        </div>
        HTML);

        $html = $this->view->render(<<<'HTML'
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

    #[Test]
    public function slots_with_hyphens(): void
    {
        $this->view->registerViewComponent('x-test', <<<'HTML'
        <div>
            <x-slot name="test-slot" />
        </div>
        HTML);

        $html = $this->view->render(<<<'HTML'
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

    #[Test]
    public function nested_table_components(): void
    {
        $this->view->registerViewComponent('x-my-table-thead', '<thead><x-slot/></thead>');
        $this->view->registerViewComponent('x-my-table-tbody', '<tbody><x-slot/></tbody>');
        $this->view->registerViewComponent('x-my-table-tr', '<tr><x-slot/></tr>');
        $this->view->registerViewComponent('x-my-table-td', '<td><x-slot/></td>');
        $this->view->registerViewComponent('x-my-table-th', '<th><x-slot/></th>');

        $html = $this->view->render(<<<'HTML'
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

    #[Test]
    public function dynamic_view_component_with_string_name(): void
    {
        $this->view->registerViewComponent('x-test', '<div>{{ $prop }}</div>');

        $html = $this->view->render(<<<'HTML'
        <x-component is="x-test" prop="test"/>
        HTML);

        $this->assertSame('<div>test</div>', $html);
    }

    #[Test]
    public function dynamic_view_component_with_expression_name(): void
    {
        $this->view->registerViewComponent('x-test', '<div>{{ $prop }}</div>');

        $html = $this->view->render(<<<'HTML'
        <x-component :is="$name" prop="test" />
        HTML, name: 'x-test');

        $this->assertSame('<div>test</div>', $html);
    }

    #[Test]
    public function dynamic_view_component_with_variable_attribute(): void
    {
        $this->view->registerViewComponent('x-test', '<div>{{ $prop }}</div>');

        $html = $this->view->render(<<<'HTML'
        <x-component :is="$name" :prop="$input" t="t" />
        HTML, name: 'x-test', input: 'test');

        $this->assertSame('<div>test</div>', $html);
    }

    #[Test]
    public function dynamic_view_component_with_object_attribute_forwarded_as_data(): void
    {
        $this->view->registerViewComponent('x-field', '<label>{{ $field->label }}</label>');

        $field = new class {
            public string $label = 'Email';
        };

        $html = $this->view->render(<<<'HTML'
        <x-component :is="'x-field'" :field="$field" />
        HTML, field: $field);

        $this->assertSame('<label>Email</label>', $html);
    }

    #[Test]
    public function dynamic_view_component_with_boolean_true_renders_bare_attribute(): void
    {
        $this->view->registerViewComponent('x-test', '<span>{{ isset($disabled) ? "yes" : "no" }}</span>');

        $html = $this->view->render(<<<'HTML'
        <x-component :is="'x-test'" :disabled="true" />
        HTML);

        $this->assertSame('<span>yes</span>', $html);
    }

    #[Test]
    public function dynamic_view_component_with_boolean_false_omits_attribute(): void
    {
        $this->view->registerViewComponent('x-test', '<span>{{ isset($disabled) ? "yes" : "no" }}</span>');

        $html = $this->view->render(<<<'HTML'
        <x-component :is="'x-test'" :disabled="false" />
        HTML);

        $this->assertSame('<span>no</span>', $html);
    }

    #[Test]
    public function dynamic_view_component_with_slot(): void
    {
        $this->view->registerViewComponent('x-test', '<div><x-slot/></div>');

        $html = $this->view->render(<<<'HTML'
        <x-component :is="$name">test</x-component>
        HTML, name: 'x-test');

        $this->assertSnippetsMatch('<div>test</div>', $html);
    }

    #[Test]
    public function dynamic_view_component_keeps_foreach_scope(): void
    {
        $this->view->registerViewComponent('x-test', '<div><x-slot/></div>');

        $html = $this->view->render(
            <<<'HTML'
            <x-component :foreach="$items as $item" :is="$name">{{ $item }}</x-component>
            HTML,
            name: 'x-test',
            items: ['a', 'b'],
        );

        $this->assertSnippetsMatch('<div>a</div><div>b</div>', $html);
    }

    #[Test]
    public function nested_slots(): void
    {
        $this->view->registerViewComponent('x-a', '<a><x-slot /></a>');
        $this->view->registerViewComponent('x-b', '<x-a><b><x-slot /></b></x-a>');

        $html = $this->view->render(<<<'HTML'
        <x-b>
            hi
        </x-b>
        HTML);

        $this->assertSnippetsMatch('<a><b>hi</b></a>', $html);
    }

    #[Test]
    public function nested_component_slot_forwarding(): void
    {
        $this->view->registerViewComponent('x-a', '<aside><x-slot name="left" /></aside><main><x-slot /></main>');
        $this->view->registerViewComponent('x-b', <<<'HTML'
        <x-a>
            <x-slot name="left">left</x-slot>
            <x-slot/>
        </x-a>
        HTML);

        $html = $this->view->render(<<<'HTML'
        <x-b>main</x-b>
        HTML);

        $this->assertSnippetsMatch('<aside>left</aside><main>main</main>', $html);
    }

    #[Test]
    public function nested_slots_with_escaping(): void
    {
        $this->view->registerViewComponent('x-a', '<a><x-slot /></a>');
        $this->view->registerViewComponent('x-b', <<<'HTML'
        <?php 
        use function \Tempest\Container\get;
        use \Tempest\Core\Environment;
        ?>
        {{ get(Environment::class)->value }}
        HTML);

        $html = $this->view->render(<<<'HTML'
        <x-a>
            <x-b />
        </x-a>
        HTML);

        $this->assertSnippetsMatch('<a>testing</a>', $html);
    }

    #[Test]
    public function repeated_local_var_across_view_components(): void
    {
        $this->view->registerViewComponent('x-test', '<div :thing="$thing">{{ $thing }}</div>');

        $html = $this->view->render(<<<'HTML'
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

    #[Test]
    public function escape_expression_attribute_in_view_components(): void
    {
        $this->view->registerViewComponent('x-test', '<div class="foo" ::escaped="foo"></div>');

        $html = $this->view->render('<x-test/>');

        $this->assertSnippetsMatch('<div class="foo" :escaped="foo"></div>', $html);
    }

    #[Test]
    public function default_slot_value(): void
    {
        $this->view->registerViewComponent('x-test', <<<'HTML'
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
            $this->view->render('<x-test>
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
            $this->view->render('<x-test>
        Overwritten
        <x-slot name="b">Overwritten B</x-slot>
        </x-test>'),
        );

        $this->assertSnippetsMatch(<<<'HTML'
        Default
        Default A
        Default B
        HTML, $this->view->render('<x-test></x-test>'));
    }

    #[Test]
    public function view_variables_are_passed_into_the_component(): void
    {
        $this->view->registerViewComponent('x-a', '<x-slot />');

        $html = $this->view->render(<<<'HTML'
        <x-a>
        {{ $title }}
        </x-a>
        HTML, title: 'test');

        $this->assertSnippetsMatch('test', $html);
    }

    #[Test]
    public function capture_outer_scope_view_component_variables(): void
    {
        $this->view->registerViewComponent('x-a', '<x-slot />');
        $this->view->registerViewComponent('x-b', '<x-slot />');

        $html = $this->view->render(
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

    #[Test]
    public function fallthrough_attribute_without_value(): void
    {
        $this->view->registerViewComponent('x-test', '<div :isset="$flag">hi</div>');

        $this->assertSnippetsMatch('', $this->view->render('<x-test />'));
        $this->assertSnippetsMatch('<div>hi</div>', $this->view->render('<x-test :flag/>'));
    }

    #[Test]
    public function imports_in_slots_from_root_node(): void
    {
        $this->view->registerViewComponent('x-test', '<div><x-slot /></div>');

        $html = $this->view->render(<<<'HTML'
        <?php
            use Tests\Tempest\Fixtures\Modules\Home\HomeController;
            use function \Tempest\Router\uri;
        ?>

        <x-test>{{ uri(HomeController::class) }}</x-test>
        HTML);

        $this->assertSame('<div>/</div>', $html);
    }

    #[Test]
    public function combined_imports_from_root_node_and_view_component(): void
    {
        $this->view->registerViewComponent('x-parent', <<<'HTML'
        <div class="parent"><x-slot /></div>
        HTML);

        $this->view->registerViewComponent('x-child', <<<'HTML'
        <?php 
        use Tests\Tempest\Fixtures\Modules\Home\HomeController; 
        ?>
        <div class="child"><x-slot /></div>
        HTML);

        $html = $this->view->render(<<<'HTML'
        <?php 
        use function \Tempest\Router\uri; 
        ?>

        <x-parent>
            <x-child>{{ uri(HomeController::class) }}</x-child>
        </x-parent>
        HTML);

        $this->assertSnippetsMatch('<div class="parent"><div class="child">/</div></div>', $html);
    }

    #[Test]
    public function exception_for_missing_imports(): void
    {
        $this->assertException(
            ViewCompilationFailed::class,
            function (): void {
                $this->view->render(view(__DIR__ . '/Fixtures/missing-import-view.view.php'));
            },
            function (ViewCompilationFailed $exception): void {
                $this->assertStringContainsString('missing-import-view.view.php', $exception->getFile());
                $this->assertSame(2, $exception->getLine());
            },
        );
    }

    #[Test]
    public function does_not_duplicate_view_compilation_frames_in_stacktrace(): void
    {
        $this->container->singleton(Environment::class, Environment::LOCAL);
        $this->container->singleton(GenericRequest::class, new GenericRequest(
            method: Method::GET,
            uri: '/',
        ));

        $viewRenderer = TempestViewRenderer::make();
        $exceptionRenderer = $this->container->get(HtmlExceptionRenderer::class);

        $this->assertException(
            ViewCompilationFailed::class,
            function () use ($viewRenderer): void {
                $viewRenderer->render(view(__DIR__ . '/Fixtures/stacktrace-standalone-error.view.php'));
            },
            function (ViewCompilationFailed $exception) use ($exceptionRenderer): void {
                $response = $exceptionRenderer->render($exception);

                $this->assertInstanceOf(DevelopmentException::class, $response);
                $this->assertInstanceOf(GenericView::class, $response->body);

                $hydration = json_decode($response->body->data['hydration'], associative: true, flags: JSON_THROW_ON_ERROR);
                $stacktrace = json_decode($hydration['stacktrace'], associative: true, flags: JSON_THROW_ON_ERROR);

                $renderCompiledFrames = array_values(array_filter(
                    $stacktrace['applicationFrames'],
                    fn (array $frame): bool => ($frame['class'] ?? null) === TempestViewRenderer::class && ($frame['function'] ?? null) === 'renderCompiled',
                ));

                $this->assertCount(1, $renderCompiledFrames);
            },
        );
    }

    #[Test]
    public function maps_component_source_line_in_view_compilation_stacktrace(): void
    {
        $this->container->singleton(Environment::class, Environment::LOCAL);
        $this->container->singleton(GenericRequest::class, new GenericRequest(
            method: Method::GET,
            uri: '/',
        ));

        $this->container->get(ViewConfig::class)->addViewComponents(
            __DIR__ . '/Fixtures/x-stacktrace-error-component.view.php',
        );

        $exceptionRenderer = $this->container->get(HtmlExceptionRenderer::class);

        $this->assertException(
            ViewCompilationFailed::class,
            function (): void {
                $this->view->render(view(__DIR__ . '/Fixtures/stacktrace-component-imported-usage.view.php'));
            },
            function (ViewCompilationFailed $exception) use ($exceptionRenderer): void {
                $response = $exceptionRenderer->render($exception);

                $this->assertInstanceOf(DevelopmentException::class, $response);
                $this->assertInstanceOf(GenericView::class, $response->body);

                $hydration = json_decode($response->body->data['hydration'], associative: true, flags: JSON_THROW_ON_ERROR);
                $stacktrace = json_decode($hydration['stacktrace'], associative: true, flags: JSON_THROW_ON_ERROR);

                $renderCompiledFrames = array_values(array_filter(
                    $stacktrace['applicationFrames'],
                    fn (array $frame): bool => ($frame['class'] ?? null) === TempestViewRenderer::class && ($frame['function'] ?? null) === 'renderCompiled',
                ));

                $this->assertCount(1, $renderCompiledFrames);
                $this->assertSame('tests/Integration/View/Fixtures/x-stacktrace-error-component.view.php', $renderCompiledFrames[0]['relativeFile']);
                $this->assertSame(2, $renderCompiledFrames[0]['line']);
            },
        );
    }

    #[Test]
    public function imports_with_nested_view_components(): void
    {
        $this->view->registerViewComponent('x-card', <<<'HTML'
        <div class="card"><x-slot /></div>
        HTML);

        $this->view->registerViewComponent('x-footer', <<<'HTML'
        <x-card><x-slot /></x-card>
        HTML);

        $html = $this->view->render(<<<'HTML'
        <?php
        use function Tempest\Router\uri;
        use Tests\Tempest\Fixtures\Modules\Home\HomeController;
        ?>
        <x-footer>
            {{ uri(HomeController::class) }}
        </x-footer>
        HTML);

        $this->assertSnippetsMatch('<div class="card">/</div>', $html);
    }

    #[Test]
    public function imports_in_nested_html_elements(): void
    {
        $this->view->registerViewComponent('x-a', '<div class="a"><x-slot /></div>">');
        $this->view->registerViewComponent('x-b', '<div class="b"><x-slot /></div>">');

        $html = $this->view->render(<<<'HTML'
        <?php
        use function Tempest\Router\uri;
        use Tests\Tempest\Fixtures\Modules\Home\HomeController;
        ?>
        <x-a>
            <div>
                <x-b>
                    {{ uri(HomeController::class) }}
                </x-b>
            </div>
        </x-a>
        HTML);

        $this->assertSnippetsMatch('<div class="a"><div><div class="b">/</div>"></div></div>">', $html);
    }

    #[Test]
    public function nested_slot_rendering(): void
    {
        $this->view->registerViewComponent('x-a', <<<'HTML'
        <div>
            <div class="left">
                <x-slot name="left"/>
            </div>

            <div class="main">
                <x-slot/>
            </div>
        </div>
        HTML);

        $this->view->registerViewComponent('x-b', <<<'HTML'
        <x-a>
            <x-slot name="left">
                left
            </x-slot>
            
            <x-slot/>
        </x-a>
        HTML);

        $html = $this->view->render(<<<'HTML'
        <x-b>
            main
        </x-b>
        HTML);

        $expected = <<<'HTML'
        <div>
            <div class="left">left</div>
            <div class="main">main</div>
        </div>
        HTML;

        $this->assertSnippetsMatch($expected, $html);
    }

    #[Test]
    public function define_slot_renders_default_content_into_child_component(): void
    {
        // <x-slot define="left"> in x-header's template is OWNED by x-header.
        // x-container has no "left" slot — it only has a default slot.
        // When the caller provides nothing for "left", the default content inside
        // the define tag is compiled and flows into x-container's default slot.
        $this->view->registerViewComponent('x-container', '<div class="container"><x-slot /></div>');
        $this->view->registerViewComponent('x-header-left', '<span>default-left</span>');
        $this->view->registerViewComponent('x-header', <<<'HTML'
        <header>
            <x-container>
                <x-slot define="left">
                    <x-header-left />
                </x-slot>
                <div>center</div>
            </x-container>
        </header>
        HTML);

        $html = $this->view->render('<x-header></x-header>');

        $this->assertSnippetsMatch(
            '<header><div class="container"><span>default-left</span><div>center</div></div></header>',
            $html,
        );
    }

    #[Test]
    public function define_slot_is_overridden_by_caller_using_name(): void
    {
        // When the caller fills the "left" slot using <x-slot name="left">, the compiled
        // caller content replaces the default and flows into x-container's default slot.
        // x-container remains unaware that "left" ever existed.
        $this->view->registerViewComponent('x-container', '<div class="container"><x-slot /></div>');
        $this->view->registerViewComponent('x-header-left', '<span>default-left</span>');
        $this->view->registerViewComponent('x-header', <<<'HTML'
        <header>
            <x-container>
                <x-slot define="left">
                    <x-header-left />
                </x-slot>
                <div>center</div>
            </x-container>
        </header>
        HTML);

        $html = $this->view->render(<<<'HTML'
        <x-header>
            <x-slot name="left"><span>custom-left</span></x-slot>
        </x-header>
        HTML);

        $this->assertSnippetsMatch(
            '<header><div class="container"><span>custom-left</span><div>center</div></div></header>',
            $html,
        );
    }

    #[Test]
    public function multiple_define_slots_alongside_static_content_in_child_component(): void
    {
        // The full x-header scenario: two define slots flanking static content,
        // all flowing as one contiguous default slot into x-container.
        $this->view->registerViewComponent('x-container', '<div class="container"><x-slot /></div>');
        $this->view->registerViewComponent('x-header-left', '<span>default-left</span>');
        $this->view->registerViewComponent('x-header-right', '<span>default-right</span>');
        $this->view->registerViewComponent('x-header', <<<'HTML'
        <header>
            <x-container>
                <x-slot define="left"><x-header-left /></x-slot>
                <div>center</div>
                <x-slot define="right"><x-header-right /></x-slot>
            </x-container>
        </header>
        HTML);

        // No caller overrides — all three pieces use defaults.
        $html = $this->view->render('<x-header></x-header>');

        $this->assertSnippetsMatch(
            '<header><div class="container"><span>default-left</span><div>center</div><span>default-right</span></div></header>',
            $html,
        );

        // Caller overrides only "right"; "left" stays default.
        $html = $this->view->render(<<<'HTML'
        <x-header>
            <x-slot name="right"><span>custom-right</span></x-slot>
        </x-header>
        HTML);

        $this->assertSnippetsMatch(
            '<header><div class="container"><span>default-left</span><div>center</div><span>custom-right</span></div></header>',
            $html,
        );
    }

    #[Test]
    public function define_slot_inside_child_named_slot_filler(): void
    {
        // <x-slot define="left"> nested inside <x-slot name="left"> (a named slot filler
        // for x-inner) evaluates x-outer's own "left" slot and places the result into
        // x-inner's "left" slot. The parent of the define token is the x-slot filler tag,
        // which is not a view component, so $parentIsComponent is false and compileSlotToken()
        // is called correctly.
        $this->view->registerViewComponent('x-inner', '<div class="inner"><x-slot name="left" /></div>');
        $this->view->registerViewComponent('x-header-left', '<span>default-left</span>');
        $this->view->registerViewComponent('x-outer', <<<'HTML'
        <div class="outer">
            <x-inner>
                <x-slot name="left">
                    <x-slot define="left"><x-header-left /></x-slot>
                </x-slot>
            </x-inner>
        </div>
        HTML);

        // No caller override — default content flows through into x-inner's named slot.
        $html = $this->view->render('<x-outer></x-outer>');

        $this->assertSnippetsMatch(
            '<div class="outer"><div class="inner"><span>default-left</span></div></div>',
            $html,
        );

        // Caller overrides "left" — custom content flows through into x-inner's named slot.
        $html = $this->view->render(<<<'HTML'
        <x-outer>
            <x-slot name="left"><span>custom-left</span></x-slot>
        </x-outer>
        HTML);

        $this->assertSnippetsMatch(
            '<div class="outer"><div class="inner"><span>custom-left</span></div></div>',
            $html,
        );
    }
}
