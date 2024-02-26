<?php

declare(strict_types=1);

namespace Tests\Tempest;

class TestingTest extends \Tempest\Testing\TestCase
{
    public function test_testing()
    {
        $this
            ->get('books')
            ->assertNotFound();

        $this
            ->get('/')
            ->assertOk();
    }
}
