<?php

namespace Integration\View\Components;

use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class SubmitComponentTest extends FrameworkIntegrationTestCase
{
    public function test_submit_without_label(): void
    {
        $this->assertSame(
            '<input type="submit" value="Submit">',
            $this->render('<x-submit />'),
        );
    }

    public function test_submit_with_label(): void
    {
        $this->assertSame(
            '<input type="submit" value="Test">',
            $this->render('<x-submit label="Test"/>'),
        );
    }
}
