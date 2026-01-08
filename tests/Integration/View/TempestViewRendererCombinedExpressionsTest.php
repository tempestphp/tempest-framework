<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\View;

use Tempest\View\ViewCache;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\View\view;

/**
 * @internal
 */
final class TempestViewRendererCombinedExpressionsTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->container->get(ViewCache::class)->clear();
    }

    public function test_if_with_data_expression(): void
    {
        $view = <<<'HTML'
        <a :if="($href ?? null) && ($label ?? null)" :href="$href">
            {{ $label }}
        </a>
        <span :elseif="$label ?? null">
            {{ $label }}
        </span>
        <div :else>Nothing</div>
        HTML;

        $html = $this->view->render(view($view, href: '#', label: 'Label'));

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'HTML'
        <a href="#">
            Label</a>
        HTML, $html);

        $html = $this->view->render(view($view, label: 'Label'));
        $this->assertStringEqualsStringIgnoringLineEndings(<<<'HTML'
        <span>
            Label</span>
        HTML, $html);

        $html = $this->view->render(view($view));
        $this->assertStringEqualsStringIgnoringLineEndings(<<<'HTML'
        <div>Nothing</div>
        HTML, $html);
    }

    public function test_foreach_with_if_and_else_expression(): void
    {
        $view = <<<'HTML'
        <div :foreach="$items as $item" :if="$label ?? null">
            {{ $label }} {{ $item }}
        </div>
        <span :else>
            No label
        </span>
        HTML;

        $html = $this->view->render(view($view, items: ['a', 'b'], label: 'Label'));
        $this->assertStringContainsString('Label a', $html);
        $this->assertStringContainsString('Label b', $html);

        $html = $this->view->render(view($view, items: ['a', 'b']));
        $this->assertStringNotContainsString('Label a', $html);
        $this->assertStringNotContainsString('Label b', $html);
        $this->assertStringContainsString('No label', $html);
    }

    public function test_foreach_with_if_and_forelse_expression(): void
    {
        $view = <<<'HTML'
        <div :if="$label ?? null" :foreach="$items as $item">
            {{ $label }} {{ $item }}
        </div>
        <span :forelse>
            No items
        </span>
        <span :else>
            No label
        </span>
        HTML;

        $html = $this->view->render(view($view));
        $this->assertStringNotContainsString('Label a', $html);
        $this->assertStringNotContainsString('Label b', $html);
        $this->assertStringNotContainsString('No items', $html);
        $this->assertStringContainsString('No label', $html);

        $html = $this->view->render(view($view, items: ['a', 'b'], label: 'Label'));
        $this->assertStringContainsString('Label a', $html);
        $this->assertStringContainsString('Label b', $html);

        $html = $this->view->render(view($view), label: 'Label');
        $this->assertStringNotContainsString('Label a', $html);
        $this->assertStringNotContainsString('Label b', $html);
        $this->assertStringContainsString('No items', $html);
    }
}
