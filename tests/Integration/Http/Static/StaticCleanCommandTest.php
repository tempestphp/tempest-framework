<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Static;

use Tempest\Core\AppConfig;
use Tempest\Drift\FrameworkIntegrationTestCase;
use Tempest\Router\Static\StaticCleanCommand;
use Tempest\Router\Static\StaticGenerateCommand;
use Tests\Tempest\Integration\Http\Static\Fixtures\StaticPageController;

use function Tempest\Support\path;

/**
 * @internal
 */
final class StaticCleanCommandTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->registerRoute(StaticPageController::class);
        $this->registerStaticPage(StaticPageController::class);
    }

    public function test_generate(): void
    {
        $appConfig = new AppConfig(baseUri: 'https://test.com');
        $this->container->config($appConfig);

        $this->console->call(StaticGenerateCommand::class);

        $this->console
            ->call(StaticCleanCommand::class)
            ->assertDoesNotContain('https://test.com/static/a/b')
            ->assertContains('/public/static/a/b/index.html')
            ->assertContains('/public/static/c/d/index.html');

        $root = $this->kernel->root;

        $this->assertFileDoesNotExist(path($root, '/public/static/a/b/index.html')->toString());
        $this->assertFileDoesNotExist(path($root, '/public/static/c/d/index.html')->toString());
        $this->assertDirectoryDoesNotExist(path($root, '/public/static')->toString());
    }
}
