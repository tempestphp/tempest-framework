<?php

namespace Tests\Tempest\Integration\View\Components;

use PHPUnit\Framework\Attributes\Test;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class MarkdownComponentTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function render_markdown_as_content(): void
    {
        $html = $this->view->render(<<<'HTML'
        <x-markdown># hi</x-markdown>
        HTML);

        $this->assertSame('<h1>hi</h1>', $html);
    }

    #[Test]
    public function render_markdown_as_variable(): void
    {
        $html = $this->view->render(<<<'HTML'
        <x-markdown :content="$text"></x-markdown>
        HTML, text: '# hi');

        $this->assertSame('<h1>hi</h1>', $html);
    }
}
