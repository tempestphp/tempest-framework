<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Static;

use function Tempest\path;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class StaticGenerateCommandTest extends FrameworkIntegrationTestCase
{
    public function test_generate(): void
    {
        $this->console
            ->call('static:generate')
            ->assertContains('/static/a/b')
            ->assertContains('/static/c/d');

        $root = $this->appConfig->root;

        $this->assertFileExists(path($root, '/public/static/a/b.html'));
        $this->assertFileExists(path($root, '/public/static/c/d.html'));

        $b = file_get_contents(path($root, '/public/static/a/b.html'));
        $d = file_get_contents(path($root, '/public/static/c/d.html'));

        $this->assertStringContainsString('a', $b);
        $this->assertStringContainsString('b', $b);

        $this->assertStringContainsString('c', $d);
        $this->assertStringContainsString('d', $d);
    }
}
