<?php

declare(strict_types=1);

namespace Integration\Http\Static;

use function Tempest\path;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class StaticCleanCommandTest extends FrameworkIntegrationTestCase
{
    public function test_generate(): void
    {
        $this->console->call('static:generate');

        $this->console->call('static:clean')
            ->assertContains('/public/static/a/b.html')
            ->assertContains('/public/static/c/d.html');

        $root = $this->appConfig->root;

        $this->assertFileDoesNotExist(path($root, '/public/static/a/b.html'));
        $this->assertFileDoesNotExist(path($root, '/public/static/c/d.html'));
    }
}
