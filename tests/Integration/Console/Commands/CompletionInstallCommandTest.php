<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Commands;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Console\CompletionRuntime;
use Tempest\Console\Enums\Shell;
use Tempest\Support\Filesystem;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class CompletionInstallCommandTest extends FrameworkIntegrationTestCase
{
    private ?string $installedFile = null;

    private ?string $metadataFile = null;

    private string $profileDirectory;

    private ?string $originalHome = null;

    private CompletionRuntime $completionRuntime;

    protected function setUp(): void
    {
        parent::setUp();

        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Shell completion is not supported on Windows.');
        }

        $this->completionRuntime = new CompletionRuntime();

        $this->originalHome = getenv('HOME') ?: null;
        $this->profileDirectory = $this->internalStorage . '/profile';

        Filesystem\ensure_directory_exists($this->profileDirectory);
        putenv("HOME={$this->profileDirectory}");
        $_ENV['HOME'] = $this->profileDirectory;
        $_SERVER['HOME'] = $this->profileDirectory;
    }

    protected function tearDown(): void
    {
        if ($this->installedFile !== null && Filesystem\is_file($this->installedFile)) {
            Filesystem\delete_file($this->installedFile);
            $this->installedFile = null;
        }

        if ($this->metadataFile !== null && Filesystem\is_file($this->metadataFile)) {
            Filesystem\delete_file($this->metadataFile);
            $this->metadataFile = null;
        }

        if ($this->originalHome === null) {
            putenv('HOME');
            unset($_ENV['HOME'], $_SERVER['HOME']);
        } else {
            putenv("HOME={$this->originalHome}");
            $_ENV['HOME'] = $this->originalHome;
            $_SERVER['HOME'] = $this->originalHome;
        }

        parent::tearDown();
    }

    #[Test]
    public function install_with_explicit_shell_flag(): void
    {
        $this->prepareCompletionRuntime();

        $this->installedFile = $this->completionRuntime->getInstalledCompletionPath(Shell::ZSH);

        $this->console
            ->call('completion:install --shell=zsh --force')
            ->assertSee('Installed completion script to:')
            ->assertSuccess();

        $installedScript = Filesystem\read_file($this->installedFile);

        $this->assertStringContainsString('/vendor/bin/tempest-complete', $installedScript);
        $this->assertStringContainsString('/.tempest/completion/commands.json', $installedScript);
    }

    #[Test]
    public function install_with_invalid_shell(): void
    {
        $this->console
            ->withoutPrompting()
            ->call('completion:install --shell=fish')
            ->assertSee('Invalid argument `fish` for `shell` argument')
            ->assertError();
    }

    #[Test]
    public function install_shows_post_install_instructions_for_zsh(): void
    {
        $this->prepareCompletionRuntime();

        $this->installedFile = $this->completionRuntime->getInstalledCompletionPath(Shell::ZSH);

        $this->console
            ->call('completion:install --shell=zsh --force')
            ->assertSee('source')
            ->assertSee('.zshrc')
            ->assertSuccess();
    }

    #[Test]
    public function install_shows_post_install_instructions_for_bash(): void
    {
        $this->prepareCompletionRuntime();

        $this->installedFile = $this->completionRuntime->getInstalledCompletionPath(Shell::BASH);

        $this->console
            ->call('completion:install --shell=bash --force')
            ->assertSee('source')
            ->assertSee('.bashrc')
            ->assertSuccess();
    }

    #[Test]
    public function install_cancelled_when_user_denies_confirmation(): void
    {
        $this->prepareCompletionRuntime();

        $this->console
            ->call('completion:install --shell=zsh')
            ->assertSee('Installing zsh completions')
            ->deny()
            ->assertSee('Installation cancelled')
            ->assertCancelled();
    }

    #[Test]
    public function install_asks_for_overwrite_when_file_exists(): void
    {
        $this->prepareCompletionRuntime();

        $targetPath = $this->completionRuntime->getInstalledCompletionPath(Shell::ZSH);
        $targetDir = $this->completionRuntime->getInstallationDirectory();

        Filesystem\create_directory($targetDir);
        Filesystem\write_file($targetPath, '# existing content');

        $this->installedFile = $targetPath;

        $this->console
            ->call('completion:install --shell=zsh')
            ->confirm()
            ->assertSee('Completion file already exists')
            ->deny()
            ->assertSee('Installation cancelled')
            ->assertCancelled();
    }

    #[Test]
    public function install_overwrites_existing_file_when_user_accepts_overwrite_default(): void
    {
        $this->prepareCompletionRuntime();

        $targetPath = $this->completionRuntime->getInstalledCompletionPath(Shell::ZSH);
        $targetDir = $this->completionRuntime->getInstallationDirectory();

        Filesystem\create_directory($targetDir);
        Filesystem\write_file($targetPath, '# existing content');

        $this->installedFile = $targetPath;

        $this->console
            ->call('completion:install --shell=zsh')
            ->confirm()
            ->assertSee('Completion file already exists')
            ->submit()
            ->assertSee('Installed completion script to:')
            ->assertSuccess();
    }

    private function prepareCompletionRuntime(): void
    {
        $this->metadataFile = $this->completionRuntime->getMetadataPath();
        Filesystem\ensure_directory_exists(dirname($this->metadataFile));
        Filesystem\write_json($this->metadataFile, ['version' => 1, 'commands' => []]);
    }
}
