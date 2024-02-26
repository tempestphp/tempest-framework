<?php

namespace Tests\Tempest;

use Tempest\Console\ConsoleStyle;

class TestingTest extends \Tempest\Testing\TestCase
{
    public function test_something()
    {
        $this
            ->console
            ->call('routes')
            ->assertContainsStyledText(ConsoleStyle::FG_BLUE('POST'));
    }
}