<?php

namespace Tests\Tempest\Integration\View\Components;

use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class MarkdownComponentTest extends FrameworkIntegrationTestCase
{
    public function test_render_markdown_as_content(): void
    {
        $html = $this->view->render(<<<'HTML'
        <x-markdown># hi</x-markdown>
        HTML);

        $this->assertSame('<h1>hi</h1>', $html);
    }

    public function test_render_markdown_as_variable(): void
    {
        $html = $this->view->render(<<<'HTML'
        <x-markdown :content="$text"></x-markdown>
        HTML, text: '# hi');

        $this->assertSame('<h1>hi</h1>', $html);
    }
}
