<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Static;

use Tempest\Core\AppConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use function Tempest\path;

/**
 * @internal
 */
final class StaticCleanCommandTest extends FrameworkIntegrationTestCase
{
    public function test_generate(): void
    {
        $appConfig = new AppConfig(baseUri: 'https://test.com');
        $this->container->config($appConfig);

        $this->console->call('static:generate');

        $this->console->call('static:clean')
            ->assertDoesNotContain('https://test.com/static/a/b')
            ->assertContains('/public/static/a/b/index.html')
            ->assertContains('/public/static/c/d/index.html');

        $root = $this->kernel->root;

        $this->assertFileDoesNotExist(path($root, '/public/static/a/b/index.html')->toString());
        $this->assertFileDoesNotExist(path($root, '/public/static/c/d/index.html')->toString());
        $this->assertDirectoryDoesNotExist(path($root, '/public/static')->toString());
    }
}
