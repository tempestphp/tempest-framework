<?php

namespace Tests\Tempest\Integration\View\Components;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Http\Method;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class FormComponentTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function form(): void
    {
        $html = $this->view->render('<x-form />');

        $this->assertStringContainsString('<form', $html);
        $this->assertStringContainsString('method="POST"', $html);
    }

    #[Test]
    public function form_with_body(): void
    {
        $html = $this->view->render('<x-form>hi</x-form>');

        $this->assertStringContainsString('hi', $html);
    }

    #[Test]
    public function form_with_string_method(): void
    {
        $html = $this->view->render('<x-form method="GET" />');

        $this->assertStringContainsString('method="GET"', $html);
    }

    #[Test]
    public function form_with_enum_method(): void
    {
        $html = $this->view->render('<x-form :method="' . Method::class . '::GET" />');

        $this->assertStringContainsString('method="GET"', $html);
    }

    #[Test]
    public function form_with_action(): void
    {
        $html = $this->view->render('<x-form action="/submit" />');

        $this->assertStringContainsString('action="/submit" method="POST"', $html);
    }

    #[Test]
    public function form_with_enctype(): void
    {
        $html = $this->view->render('<x-form enctype="application/x-www-form-urlencoded" />');

        $this->assertStringContainsString('enctype="application/x-www-form-urlencoded"', $html);
    }
}
