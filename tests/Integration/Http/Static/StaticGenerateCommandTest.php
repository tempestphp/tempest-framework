<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Static;

use Tempest\Console\ExitCode;
use Tempest\Core\AppConfig;
use Tempest\Router\Static\StaticGenerateCommand;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
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
        $this->container->config(new AppConfig(baseUri: 'https://test.com'));

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

        $this->container->config(new AppConfig(baseUri: 'https://test.com'));

        $this->console
            ->call(StaticGenerateCommand::class)
            ->assertSee('HTTP 500')
            ->assertExitCode(ExitCode::ERROR);
    }

    public function test_failure_no_textual_content(): void
    {
        $this->registerRoute([StaticPageController::class, 'noTextualContent']);
        $this->registerStaticPage([StaticPageController::class, 'noTextualContent']);

        $this->container->config(new AppConfig(baseUri: 'https://test.com'));

        $this->console
            ->call(StaticGenerateCommand::class)
            ->assertSee('NO CONTENT')
            ->assertExitCode(ExitCode::ERROR);
    }

    public function test_failure_no_build(): void
    {
        $this->registerRoute([StaticPageController::class, 'vite']);
        $this->registerStaticPage([StaticPageController::class, 'vite']);

        $this->container->config(new AppConfig(baseUri: 'https://test.com'));

        $this->console
            ->call(StaticGenerateCommand::class)
            ->assertSee('A Vite build is needed for [/static/vite/a/b]')
            ->assertExitCode(ExitCode::ERROR);
    }

    public function test_dead_link(): void
    {
        $this->registerRoute([StaticPageController::class, 'deadLink']);
        $this->registerStaticPage([StaticPageController::class, 'deadLink']);

        $this->container->config(new AppConfig(baseUri: 'https://test.com'));

        $this->console
            ->call(StaticGenerateCommand::class)
            ->assertSee('1 DEAD LINK')
            ->assertSee('https://test.com/404')
            ->assertExitCode(ExitCode::ERROR);
    }

    public function test_allow_dead_links(): void
    {
        $this->registerRoute([StaticPageController::class, 'deadLink']);
        $this->registerStaticPage([StaticPageController::class, 'deadLink']);

        $this->container->config(new AppConfig(baseUri: 'https://test.com'));

        $this->console
            ->call(StaticGenerateCommand::class, ['--allow-dead-links' => true])
            ->assertExitCode(ExitCode::SUCCESS);
    }

    public function test_external_dead_links(): void
    {
        $this->registerRoute([StaticPageController::class, 'deadLink']);
        $this->registerStaticPage([StaticPageController::class, 'deadLink']);

        $this->container->config(new AppConfig(baseUri: 'https://test.com'));

        $this->console
            ->call(StaticGenerateCommand::class, ['--allow-external-dead-links' => false])
            ->assertSee('2 DEAD LINKS')
            ->assertSee('https://test.com/404')
            ->assertSee('https://google.com/404')
            ->assertExitCode(ExitCode::ERROR);
    }

    public function test_ignore_dead_links(): void
    {
        $this->registerRoute([StaticPageController::class, 'allowedDeadLink']);
        $this->registerStaticPage([StaticPageController::class, 'allowedDeadLink']);

        $this->container->config(new AppConfig(baseUri: 'https://test.com'));

        $this->console
            ->call(StaticGenerateCommand::class)
            ->assertExitCode(ExitCode::SUCCESS);
    }
}
