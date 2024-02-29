<?php

declare(strict_types=1);

namespace Tests\Tempest;

use Tempest\Console\ConsoleStyle;
use Tempest\Testing\TestCase;

class TestingTest extends TestCase
{
    public function test_something()
    {
        $this
            ->console
            ->call('routes')
            ->assertContains('GET  /test')
            ->assertContainsFormattedText(ConsoleStyle::FG_BLUE('GET '));
    }
}
