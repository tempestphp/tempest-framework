<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\View;

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
            object: new class {
            },
        );
    }

    public function test_expression_attribute_with_object_on_view_component(): void
    {
        // <x-button :href="$object" />      💯 always pass as variable, never set directly as attribute

        $this->registerViewComponent('x-link', <<<'HTML'
        <a :href="$object->url"><x-slot/></a>
        HTML);

        $this->assertSame(
            '<a href="https://">a</a>',
            $this->render(
                <<<'HTML'
            <x-link :object="$object">a</x-link>
            HTML,
                object: new class {
                    public string $url = 'https://';
                },
            ),
        );
    }

    public function test_expression_attribute_on_view_component(): void
    {
        // <x-button :href="$href" />        💯 always pass as variable, never set directly as attribute

        $this->registerViewComponent('x-link', <<<'HTML'
        <a :href="$href"><x-slot/></a>
        HTML);

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

        $this->registerViewComponent('x-link', <<<'HTML'
        <a :href="$href"><x-slot/></a>
        HTML);

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

        $this->registerViewComponent('x-link', <<<'HTML'
        <a :href="$href->url"><x-slot/></a>
        HTML);

        /* There's a name collision here:
            <?php $href = $href->url ?? null; ?>\n
            <a href="<?= $href->url ?>">a</a>\n
            <?php unset($href); ?>\n
         */

        $this->assertSame(
            '<a href="https://">a</a>',
            $this->render(
                <<<'HTML'
            <x-link :href="$object">a</x-link>
            HTML,
                object: new class {
                    public string $url = 'https://';
                },
            ),
        );
    }
}
