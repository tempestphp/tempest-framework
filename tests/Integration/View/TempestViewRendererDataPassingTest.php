<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\View;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tempest\View\Exceptions\ExpressionAttributeWasInvalid;
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

    #[Test]
    public function normal_attribute(): void
    {
        // <a href="http://"                    💯 <a href="https://"
        $this->assertSame(
            '<a href="https://">a</a>',
            $this->view->render(
                '<a href="https://">a</a>',
            ),
        );
    }

    #[Test]
    public function expression_attribute_with_variable(): void
    {
        // <a :href="$href"                     💯 <a href="https://"
        $this->assertSame(
            '<a href="https://">a</a>',
            $this->view->render(
                '<a :href="$href">a</a>',
                href: 'https://',
            ),
        );
    }

    #[Test]
    public function expression_attribute_with_expression(): void
    {
        // <a :href="strtoupper('string')"      💯 <a href="HTTPS://"
        $this->assertSame(
            '<a href="HTTPS://">a</a>',
            $this->view->render(
                '<a :href="strtoupper($href)">a</a>',
                href: 'https://',
            ),
        );
    }

    #[Test]
    public function normal_attribute_with_php_short_echo(): void
    {
        // <a href="<?= $href "                 💯 <a href="https://"
        $this->assertSame(
            '<a href="https://">a</a>',
            $this->view->render(
                <<<'HTML'
                <a href="<?= $href ?>">a</a>
                HTML,
                href: 'https://',
            ),
        );
    }

    #[Test]
    public function normal_attribute_with_view_echo(): void
    {
        // <a href="{{ $href }}"                💯 <a href="https://&amp;"
        $this->assertSame(
            '<a href="https://&amp;">a</a>',
            $this->view->render(
                <<<'HTML'
                <a href="{{ $href }}">a</a>
                HTML,
                href: 'https://&',
            ),
        );
    }

    #[Test]
    public function normal_attribute_with_raw_view_echo(): void
    {
        // <a href="{!! $href !!}"                💯 <a href="https://&"
        $this->assertSame(
            '<a href="https://&">a</a>',
            $this->view->render(
                <<<'HTML'
                <a href="{!! $href !!}">a</a>
                HTML,
                href: 'https://&',
            ),
        );
    }

    #[Test]
    public function expression_attribute_with_view_echo_not_allowed(): void
    {
        // <a :href="{{ $href }}"               ❌ exception
        $this->expectException(ExpressionAttributeWasInvalid::class);

        $this->view->render(
            <<<'HTML'
            <a :href="{{ $href }}">a</a>
            HTML,
        );
    }

    #[Test]
    public function expression_attribute_with_raw_view_echo_not_allowed(): void
    {
        // <a :href="{!! $href !!}"               ❌ exception
        $this->expectException(ExpressionAttributeWasInvalid::class);

        $this->view->render(
            <<<'HTML'
            <a :href="{!! $href !!}">a</a>
            HTML,
        );
    }

    #[Test]
    public function expression_attribute_with_short_php_echo_not_allowed(): void
    {
        // <a :href="<?= $href …"               ❌ exception
        $this->expectException(ExpressionAttributeWasInvalid::class);

        $this->view->render(
            <<<'HTML'
            <a :href="<?= $href ?>">a</a>
            HTML,
        );
    }

    #[Test]
    public function expression_attribute_with_object_without_view_component_not_allowed(): void
    {
        // <a :href="$object"                   ❌ exception
        $this->expectException(ExpressionAttributeWasInvalid::class);

        $this->view->render(
            <<<'HTML'
            <a :href="<?= $object ?>">a</a>
            HTML,
            object: new class() {},
        );
    }

    #[Test]
    public function expression_attribute_with_object_on_view_component(): void
    {
        // <x-button :href="$object" />      💯 always pass as variable, never set directly as attribute

        $this->view->registerViewComponent(
            'x-link',
            <<<'HTML'
            <a :href="$object->url"><x-slot/></a>
            HTML,
        );

        $this->assertSame(
            '<a href="https://">a</a>',
            $this->view->render(
                <<<'HTML'
                <x-link :object="$object">a</x-link>
                HTML,
                object: new class() {
                    public string $url = 'https://';
                },
            ),
        );
    }

    #[Test]
    public function expression_attribute_on_view_component(): void
    {
        // <x-button :href="$href" />        💯 always pass as variable, never set directly as attribute

        $this->view->registerViewComponent(
            'x-link',
            <<<'HTML'
            <a :href="$href"><x-slot/></a>
            HTML,
        );

        $this->assertSame(
            '<a href="https://">a</a>',
            $this->view->render(
                <<<'HTML'
                <x-link :href="$href">a</x-link>
                HTML,
                href: 'https://',
            ),
        );
    }

    #[Test]
    public function zero_expression_attribute_literal_on_view_component(): void
    {
        $this->view->registerViewComponent(
            'x-link',
            <<<'HTML'
            <a :href="$href" :data-start-percent="$attributes->get('data-start-percent')"><x-slot/></a>
            HTML,
        );

        $this->assertSame(
            '<a href="0" data-start-percent="0">a</a>',
            $this->view->render(
                <<<'HTML'
                <x-link :href="0" :data-start-percent="0">a</x-link>
                HTML,
            ),
        );
    }

    #[Test]
    public function normal_attribute_on_view_component(): void
    {
        // <x-button href="http://…" />      💯 always pass as variable, never set directly as attribute

        $this->view->registerViewComponent(
            'x-link',
            <<<'HTML'
            <a :href="$href"><x-slot/></a>
            HTML,
        );

        $this->assertSame(
            '<a href="https://">a</a>',
            $this->view->render(
                <<<'HTML'
                <x-link href="https://">a</x-link>
                HTML,
            ),
        );
    }

    #[Test]
    public function expression_attribute_with_same_name(): void
    {
        // <x-button :href="$object" />      💯 always pass as variable, never set directly as attribute

        $this->view->registerViewComponent(
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
            $this->view->render(
                <<<'HTML'
                <x-link :href="$object">a</x-link>
                HTML,
                object: new class() {
                    public string $url = 'https://';
                },
            ),
        );
    }

    #[Test]
    public function boolean_attributes(): void
    {
        $this->assertSame(
            '<option value="value" selected>name</option>',
            $this->view->render(<<<'HTML'
            <option value="<?= $value ?>" :selected="$selected"><?= $name ?></option>
            HTML, value: 'value', selected: true, name: 'name'),
        );

        $this->assertSame(
            '<option value="value" >name</option>',
            $this->view->render(<<<'HTML'
            <option value="<?= $value ?>" :selected="$selected"><?= $name ?></option>
            HTML, value: 'value', selected: false, name: 'name'),
        );

        $this->assertSame(
            '<textarea autofocus></textarea>',
            $this->view->render('<textarea autofocus></textarea>'),
        );
    }

    #[TestWith(['false'])]
    #[TestWith(['null'])]
    #[TestWith(["''"])]
    #[TestWith(['$show'])]
    #[Test]
    public function falsy_bool_attribute(mixed $value): void
    {
        $html = $this->view->render(<<<HTML
        <div :data-active="{$value}"></div>
        HTML, show: false);

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'HTML'
        <div ></div>
        HTML, $html);
    }

    #[TestWith([0])]
    #[TestWith([0.0])]
    #[TestWith(['0'])]
    #[Test]
    public function zero_expression_attribute_value_is_rendered(mixed $value): void
    {
        $html = $this->view->render(<<<'HTML'
        <div :data-start-percent="$value"></div>
        HTML, value: $value);

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'HTML'
        <div data-start-percent="0"></div>
        HTML, $html);
    }

    #[TestWith(['true'])]
    #[TestWith(['$show'])]
    #[Test]
    public function truthy_bool_attribute(mixed $value): void
    {
        $html = $this->view->render(<<<HTML
        <div :data-active="{$value}"></div>
        HTML, show: true);

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'HTML'
        <div data-active></div>
        HTML, $html);
    }

    #[Test]
    public function multiple_boolean_attribute(): void
    {
        $html = $this->view->render(<<<HTML
        <div :data-a="false" :data-b="false" :data-c="true"></div>
        HTML);

        $this->assertSnippetsMatch(<<<'HTML'
        <div data-c></div>
        HTML, $html);
    }

    #[Test]
    public function expression_attribute_in_raw_element(): void
    {
        $this->view->registerViewComponent(
            'x-test',
            <<<'HTML'
            <div><x-slot/></div>
            HTML,
        );

        $html = $this->view->render(<<<'HTML'
        <x-test :language="$language">
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

    #[Test]
    public function echo_in_attributes(): void
    {
        $this->assertSame(
            '<div class="hi hi hi"></div>',
            $this->view->render(<<<HTML
            <div class="hi {{ 'hi' }} hi">
            HTML),
        );

        $this->assertSame(
            '<div class="hi hi hi"></div>',
            $this->view->render(<<<HTML
            <div class="hi {!! 'hi' !!} hi">
            HTML),
        );
    }

    #[Test]
    public function boolean_attributes_in_view_component(): void
    {
        $this->view->registerViewComponent('x-test', <<<HTML
        <div>
            <x-slot/>
        </div>
        HTML);

        $html = $this->view->render(<<<'HTML'
        <x-test>
            <a :href="'hi'"></a>
        </x-test>
        HTML);

        $this->assertStringContainsString(' href="hi"', $html);
    }

    #[Test]
    public function global_variables_are_kept(): void
    {
        $this->view->registerViewComponent('x-test', <<<'HTML'
        <div>{{ $item }}</div>
        HTML);

        $html = $this->view->render(<<<'HTML'
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
