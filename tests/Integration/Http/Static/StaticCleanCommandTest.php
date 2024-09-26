<?php

declare(strict_types=1);

namespace Integration\Http\Static;

use Tempest\Core\AppConfig;
use function Tempest\path;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

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

        $this->assertFileDoesNotExist(path($root, '/public/static/a/b/index.html'));
        $this->assertFileDoesNotExist(path($root, '/public/static/c/d/index.html'));
    }
}
