<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Static;

use Tempest\Core\AppConfig;
use function Tempest\path;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class StaticGenerateCommandTest extends FrameworkIntegrationTestCase
{
    public function test_static_site_generate_command(): void
    {
        $appConfig = new AppConfig(baseUri: 'https://test.com');
        $this->container->config($appConfig);

        $this->console
            ->call('static:generate')
            ->assertContains('/static/a/b')
            ->assertDoesNotContain('https://test.com/static/a/b')
            ->assertContains('/static/c/d');

        $root = $this->kernel->root;

        $this->assertFileExists(path($root, '/public/static/a/b/index.html')->toString());
        $this->assertFileExists(path($root, '/public/static/c/d/index.html')->toString());

        $b = file_get_contents(path($root, '/public/static/a/b/index.html')->toString());
        $d = file_get_contents(path($root, '/public/static/c/d/index.html')->toString());

        $this->assertStringContainsString('a', $b);
        $this->assertStringContainsString('b', $b);

        $this->assertStringContainsString('c', $d);
        $this->assertStringContainsString('d', $d);
    }
}
