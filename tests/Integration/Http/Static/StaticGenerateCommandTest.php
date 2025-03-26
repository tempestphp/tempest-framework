<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Static;

use Tempest\Core\AppConfig;
use Tempest\Router\Static\StaticGenerateCommand;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\root_path;

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
            ->call(StaticGenerateCommand::class)
            ->assertContains('/static/a/b')
            ->assertDoesNotContain('https://test.com/static/a/b')
            ->assertContains('/static/c/d');

        $root = $this->kernel->root;

        $this->assertFileExists(root_path($root, '/public/static/a/b/index.html'));
        $this->assertFileExists(root_path($root, '/public/static/c/d/index.html'));

        $b = file_get_contents(root_path($root, '/public/static/a/b/index.html'));
        $d = file_get_contents(root_path($root, '/public/static/c/d/index.html'));

        $this->assertStringContainsString('a', $b);
        $this->assertStringContainsString('b', $b);

        $this->assertStringContainsString('c', $d);
        $this->assertStringContainsString('d', $d);
    }
}
