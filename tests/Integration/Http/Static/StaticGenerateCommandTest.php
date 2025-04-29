<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Static;

use Tempest\Console\ExitCode;
use Tempest\Core\AppConfig;
use Tempest\Drift\FrameworkIntegrationTestCase;
use Tempest\Router\Static\StaticGenerateCommand;
use Tests\Tempest\Integration\Http\Static\Fixtures\StaticPageController;

use function Tempest\root_path;

/**
 * @internal
 */
final class StaticGenerateCommandTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->registerRoute(StaticPageController::class);
        $this->registerStaticPage(StaticPageController::class);
    }

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

    public function test_failure_status_code(): void
    {
        $this->registerRoute([StaticPageController::class, 'http500']);
        $this->registerStaticPage([StaticPageController::class, 'http500']);

        $appConfig = new AppConfig(baseUri: 'https://test.com');
        $this->container->config($appConfig);

        $this->console
            ->call(StaticGenerateCommand::class)
            ->assertSee('HTTP 500')
            ->assertExitCode(ExitCode::ERROR);
    }

    public function test_failure_no_textual_content(): void
    {
        $this->registerRoute([StaticPageController::class, 'noTextualContent']);
        $this->registerStaticPage([StaticPageController::class, 'noTextualContent']);

        $appConfig = new AppConfig(baseUri: 'https://test.com');
        $this->container->config($appConfig);

        $this->console
            ->call(StaticGenerateCommand::class)
            ->assertSee('NO CONTENT')
            ->assertExitCode(ExitCode::ERROR);
    }

    public function test_failure_no_build(): void
    {
        $this->registerRoute([StaticPageController::class, 'vite']);
        $this->registerStaticPage([StaticPageController::class, 'vite']);

        $appConfig = new AppConfig(baseUri: 'https://test.com');
        $this->container->config($appConfig);

        $this->console
            ->call(StaticGenerateCommand::class)
            ->assertSee('A Vite build is needed for [/static/vite/a/b]')
            ->assertExitCode(ExitCode::ERROR);
    }
}
