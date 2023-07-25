<?php

declare(strict_types=1);

namespace Tests\Tempest\Application;

use Tests\Tempest\TestCase;

class ConsoleApplicationTest extends TestCase
{
    /** @test */
    public function test_cli_application()
    {
        $output = $this->console('hello:world input');

        $this->assertSame(
            ['Hi', 'input'],
            $output->lines,
        );
    }
}
