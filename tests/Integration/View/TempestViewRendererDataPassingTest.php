<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\View;

use PHPUnit\Framework\Attributes\TestWith;
use Tempest\View\Exceptions\InvalidExpressionAttribute;
use Tempest\View\ViewCache;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class TempestViewRendererDataPassingTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->container->get(ViewCache::class)->clear();
    }

    public function test_normal_attribute(): void
    {
        // <a href="http://"                    💯 <a href="https://"
        $this->assertSame(
            '<a href="https://">a</a>',
            $this->render(
                '<a href="https://">a</a>',
            ),
        );
    }

    public function test_expression_attribute_with_variable(): void
    {
        // <a :href="$href"                     💯 <a href="https://"
        $this->assertSame(
            '<a href="https://">a</a>',
            $this->render(
                '<a :href="$href">a</a>',
                href: 'https://',
            ),
        );
    }

    public function test_expression_attribute_with_expression(): void
    {
        // <a :href="strtoupper('string')"      💯 <a href="HTTPS://"
        $this->assertSame(
            '<a href="HTTPS://">a</a>',
            $this->render(
                '<a :href="strtoupper($href)">a</a>',
                href: 'https://',
            ),
        );
    }

    public function test_normal_attribute_with_php_short_echo(): void
    {
        // <a href="<?= $href "                 💯 <a href="https://"
        $this->assertSame(
            '<a href="https://">a</a>',
            $this->render(
                <<<'HTML'
                <a href="<?= $href ?>">a</a>
                HTML,
                href: 'https://',
            ),
        );
    }

    public function test_normal_attribute_with_view_echo(): void
    {
        // <a href="{{ $href }}"                💯 <a href="https://&amp;"
        $this->assertSame(
            '<a href="https://&amp;">a</a>',
            $this->render(
                <<<'HTML'
                <a href="{{ $href }}">a</a>
                HTML,
                href: 'https://&',
            ),
        );
    }

    public function test_normal_attribute_with_raw_view_echo(): void
    {
        // <a href="{!! $href !!}"                💯 <a href="https://&"
        $this->assertSame(
            '<a href="https://&">a</a>',
            $this->render(
                <<<'HTML'
                <a href="{!! $href !!}">a</a>
                HTML,
                href: 'https://&',
            ),
        );
    }

    public function test_expression_attribute_with_view_echo_not_allowed(): void
    {
        // <a :href="{{ $href }}"               ❌ exception
        $this->expectException(InvalidExpressionAttribute::class);

        $this->render(
            <<<'HTML'
            <a :href="{{ $href }}">a</a>
            HTML,
        );
    }

    public function test_expression_attribute_with_raw_view_echo_not_allowed(): void
    {
        // <a :href="{!! $href !!}"               ❌ exception
        $this->expectException(InvalidExpressionAttribute::class);

        $this->render(
            <<<'HTML'
            <a :href="{!! $href !!}">a</a>
            HTML,
        );
    }

    public function test_expression_attribute_with_short_php_echo_not_allowed(): void
    {
        // <a :href="<?= $href …"               ❌ exception
        $this->expectException(InvalidExpressionAttribute::class);

        $this->render(
            <<<'HTML'
            <a :href="<?= $href ?>">a</a>
            HTML,
        );
    }

    public function test_expression_attribute_with_object_without_view_component_not_allowed(): void
    {
        // <a :href="$object"                   ❌ exception
        $this->expectException(InvalidExpressionAttribute::class);

        $this->render(
            <<<'HTML'
            <a :href="<?= $object ?>">a</a>
            HTML,
            object: new class() {},
        );
    }

    public function test_expression_attribute_with_object_on_view_component(): void
    {
        // <x-button :href="$object" />      💯 always pass as variable, never set directly as attribute

        $this->registerViewComponent(
            'x-link',
            <<<'HTML'
            <a :href="$object->url"><x-slot/></a>
            HTML,
        );

        $this->assertSame(
            '<a href="https://">a</a>',
            $this->render(
                <<<'HTML'
                <x-link :object="$object">a</x-link>
                HTML,
                object: new class() {
                    public string $url = 'https://';
                },
            ),
        );
    }

    public function test_expression_attribute_on_view_component(): void
    {
        // <x-button :href="$href" />        💯 always pass as variable, never set directly as attribute

        $this->registerViewComponent(
            'x-link',
            <<<'HTML'
            <a :href="$href"><x-slot/></a>
            HTML,
        );

        $this->assertSame(
            '<a href="https://">a</a>',
            $this->render(
                <<<'HTML'
                <x-link :href="$href">a</x-link>
                HTML,
                href: 'https://',
            ),
        );
    }

    public function test_normal_attribute_on_view_component(): void
    {
        // <x-button href="http://…" />      💯 always pass as variable, never set directly as attribute

        $this->registerViewComponent(
            'x-link',
            <<<'HTML'
            <a :href="$href"><x-slot/></a>
            HTML,
        );

        $this->assertSame(
            '<a href="https://">a</a>',
            $this->render(
                <<<'HTML'
                <x-link href="https://">a</x-link>
                HTML,
            ),
        );
    }

    public function test_expression_attribute_with_same_name(): void
    {
        // <x-button :href="$object" />      💯 always pass as variable, never set directly as attribute

        $this->registerViewComponent(
            'x-link',
            <<<'HTML'
            <a :href="$href->url"><x-slot/></a>
            HTML,
        );

        /* There's a name collision here:
         * <?php $href = $href->url ?? null; ?>
         * <a href="<?= $href->url ?>">a</a>
         * <?php unset($href); ?>
         * So instead we do this:
         * <?php $href ??= $href->url ?? null; ?>
         */

        $this->assertSame(
            '<a href="https://">a</a>',
            $this->render(
                <<<'HTML'
                <x-link :href="$object">a</x-link>
                HTML,
                object: new class() {
                    public string $url = 'https://';
                },
            ),
        );
    }

    public function test_boolean_attributes(): void
    {
        $this->assertSame(
            '<option value="value" selected>name</option>',
            $this->render(<<<'HTML'
            <option value="<?= $value ?>" :selected="$selected"><?= $name ?></option>
            HTML, value: 'value', selected: true, name: 'name'),
        );

        $this->assertSame(
            '<option value="value" >name</option>',
            $this->render(<<<'HTML'
            <option value="<?= $value ?>" :selected="$selected"><?= $name ?></option>
            HTML, value: 'value', selected: false, name: 'name'),
        );

        $this->assertSame(
            '<textarea autofocus></textarea>',
            $this->render('<textarea autofocus></textarea>'),
        );
    }

    #[TestWith(['false'])]
    #[TestWith(['null'])]
    #[TestWith(['0'])]
    #[TestWith(['$show'])]
    public function test_falsy_bool_attribute(mixed $value): void
    {
        $html = $this->render(<<<HTML
        <div :data-active="{$value}"></div>
        HTML, show: false);

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'HTML'
        <div ></div>
        HTML, $html);
    }

    #[TestWith(['true'])]
    #[TestWith(['$show'])]
    public function test_truthy_bool_attribute(mixed $value): void
    {
        $html = $this->render(<<<HTML
        <div :data-active="{$value}"></div>
        HTML, show: true);

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'HTML'
        <div data-active></div>
        HTML, $html);
    }

    public function test_multiple_boolean_attribute(): void
    {
        $html = $this->render(<<<HTML
        <div :data-a="false" :data-b="false" :data-c="true"></div>
        HTML);

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'HTML'
        <div data-c></div>
        HTML, $html);
    }

    public function test_expression_attribute_in_raw_element(): void
    {
        $this->registerViewComponent(
            'x-test',
            <<<'HTML'
            <div><x-slot/></div>
            HTML,
        );

        $html = $this->render(<<<'HTML'
        <x-test>
            <pre :data-lang="$language"><hello></hello>foo<p>bar</p></pre>
        </x-test>
        HTML, language: 'php');

        $this->assertSnippetsMatch(
            <<<'HTML'
            <div><pre data-lang="php"><hello></hello>foo<p>bar</p></pre></div>
            HTML,
            $html,
        );
    }

    public function test_echo_in_attributes(): void
    {
        $this->assertSame(
            '<div class="hi hi hi"></div>',
            $this->render(<<<HTML
            <div class="hi {{ 'hi' }} hi">
            HTML),
        );

        $this->assertSame(
            '<div class="hi hi hi"></div>',
            $this->render(<<<HTML
            <div class="hi {!! 'hi' !!} hi">
            HTML),
        );
    }

    private function assertSnippetsMatch(string $expected, string $actual): void
    {
        $expected = str_replace([PHP_EOL, ' '], '', $expected);
        $actual = str_replace([PHP_EOL, ' '], '', $actual);

        $this->assertSame($expected, $actual);
    }

    public function test_boolean_attributes_in_view_component(): void
    {
        $this->registerViewComponent('x-test', <<<HTML
        <div>
            <x-slot/>
        </div>
        HTML);

        $html = $this->render(<<<'HTML'
        <x-test>
            <a :href="'hi'"></a>
        </x-test>
        HTML);

        $this->assertStringContainsString(' href="hi"', $html);
    }

    public function test_global_variables_are_kept(): void
    {
        $this->registerViewComponent('x-test', <<<'HTML'
        <div>{{ $item }}</div>
        HTML);

        $html = $this->render(<<<'HTML'
        <x-test :item="$item"></x-test>
        <x-test :item="$item"></x-test>
        <x-test :item="$item"></x-test>
        HTML, item: 'foo');

        $this->assertSnippetsMatch(<<<'HTML'
        <div>foo</div>
        <div>foo</div>
        <div>foo</div>
        HTML, $html);
    }
}
