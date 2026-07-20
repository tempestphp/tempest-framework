<?php

namespace Tests\Tempest\Integration\View\Components;

use PHPUnit\Framework\Attributes\Test;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class SubmitComponentTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function submit_without_label(): void
    {
        $this->assertSame(
            '<input type="submit" value="Submit">',
            $this->view->render('<x-submit />'),
        );
    }

    #[Test]
    public function submit_with_label(): void
    {
        $this->assertSame(
            '<input type="submit" value="Test">',
            $this->view->render('<x-submit label="Test"/>'),
        );
    }
}
