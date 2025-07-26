<?php

namespace Integration\View\Components;

use Tempest\Http\Method;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class FormComponentTest extends FrameworkIntegrationTestCase
{
    public function test_form(): void
    {
        $html = $this->render('<x-form />');

        $this->assertSnippetsMatch('<form method="POST"></form>', $html);
    }

    public function test_form_with_body(): void
    {
        $html = $this->render('<x-form>hi</x-form>');

        $this->assertSnippetsMatch('<form method="POST">hi</form>', $html);
    }

    public function test_form_with_string_method(): void
    {
        $html = $this->render('<x-form method="GET" />');

        $this->assertSnippetsMatch('<form method="GET"></form>', $html);
    }

    public function test_form_with_enum_method(): void
    {
        $html = $this->render('<x-form :method="' . Method::class . '::GET" />');

        $this->assertSnippetsMatch('<form method="GET"></form>', $html);
    }

    public function test_form_with_action(): void
    {
        $html = $this->render('<x-form action="/submit" />');

        $this->assertSnippetsMatch('<form method="POST" action="/submit"></form>', $html);
    }

    public function test_form_with_enctype(): void
    {
        $html = $this->render('<x-form enctype="application/x-www-form-urlencoded" />');

        $this->assertStringContainsString('enctype="application/x-www-form-urlencoded"', $html);
    }
}
