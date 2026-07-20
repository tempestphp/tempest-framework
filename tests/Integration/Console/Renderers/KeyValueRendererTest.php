<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Renderers;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Console\Components\Renderers\KeyValueRenderer;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class KeyValueRendererTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function render_line(): void
    {
        $renderer = new KeyValueRenderer();
        $rendered = $renderer->render('Foo', 'bar');

        $this->assertSame(
            'Foo <style="fg-gray dim">.....................................................................................................................</style> bar',
            $rendered,
        );
    }

    #[Test]
    public function render_total_width_smaller_than_text(): void
    {
        $renderer = new KeyValueRenderer();
        $rendered = $renderer->render('Some long text', str_repeat('a', KeyValueRenderer::MAX_WIDTH));

        $this->assertSame(
            'Some long text <style="fg-gray dim">...</style> aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            $rendered,
        );
    }
}
