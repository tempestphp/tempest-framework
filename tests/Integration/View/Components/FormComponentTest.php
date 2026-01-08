<?php

namespace Tests\Tempest\Integration\View\Components;

use Tempest\Http\Method;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class FormComponentTest extends FrameworkIntegrationTestCase
{
    public function test_form(): void
    {
        $html = $this->render('<x-form />');

        $this->assertStringContainsString('<form', $html);
        $this->assertStringContainsString('method="POST"', $html);
    }

    public function test_form_with_body(): void
    {
        $html = $this->render('<x-form>hi</x-form>');

        $this->assertStringContainsString('hi', $html);
    }

    public function test_form_with_string_method(): void
    {
        $html = $this->render('<x-form method="GET" />');

        $this->assertStringContainsString('method="GET"', $html);
    }

    public function test_form_with_enum_method(): void
    {
        $html = $this->render('<x-form :method="' . Method::class . '::GET" />');

        $this->assertStringContainsString('method="GET"', $html);
    }

    public function test_form_with_action(): void
    {
        $html = $this->render('<x-form action="/submit" />');

        $this->assertStringContainsString('action="/submit" method="POST"', $html);
    }

    public function test_form_with_enctype(): void
    {
        $html = $this->render('<x-form enctype="application/x-www-form-urlencoded" />');

        $this->assertStringContainsString('enctype="application/x-www-form-urlencoded"', $html);
    }
}
