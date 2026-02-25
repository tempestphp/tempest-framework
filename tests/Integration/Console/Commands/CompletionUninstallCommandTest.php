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
final class CompletionUninstallCommandTest extends FrameworkIntegrationTestCase
{
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
    public function uninstall_with_explicit_shell_flag(): void
    {
        $targetPath = $this->completionRuntime->getInstalledCompletionPath(Shell::ZSH);
        $targetDir = $this->completionRuntime->getInstallationDirectory();

        Filesystem\create_directory($targetDir);
        Filesystem\write_file($targetPath, '# completion script');

        $this->console
            ->call('completion:uninstall --shell=zsh --force')
            ->assertSee('Removed completion script:')
            ->assertSee('tempest.zsh')
            ->assertSuccess();

        $this->assertFalse(Filesystem\is_file($targetPath));
    }

    #[Test]
    public function uninstall_with_invalid_shell(): void
    {
        $this->console
            ->withoutPrompting()
            ->call('completion:uninstall --shell=fish')
            ->assertSee('Invalid argument `fish` for `shell` argument')
            ->assertError();
    }

    #[Test]
    public function uninstall_when_file_not_exists(): void
    {
        $targetPath = $this->completionRuntime->getInstalledCompletionPath(Shell::ZSH);

        if (Filesystem\is_file($targetPath)) {
            Filesystem\delete_file($targetPath);
        }

        $this->console
            ->withoutPrompting()
            ->call('completion:uninstall --shell=zsh --force')
            ->assertSee('Completion file not found')
            ->assertSee('Nothing to uninstall')
            ->assertSuccess();
    }

    #[Test]
    public function uninstall_shows_config_file_reminder(): void
    {
        $targetPath = $this->completionRuntime->getInstalledCompletionPath(Shell::BASH);
        $targetDir = $this->completionRuntime->getInstallationDirectory();

        Filesystem\create_directory($targetDir);
        Filesystem\write_file($targetPath, '# completion script');

        $this->console
            ->call('completion:uninstall --shell=bash --force')
            ->assertSee('Remember to remove any related lines')
            ->assertSee('.bashrc')
            ->assertSuccess();
    }

    #[Test]
    public function uninstall_cancelled_when_user_denies_confirmation(): void
    {
        $targetPath = $this->completionRuntime->getInstalledCompletionPath(Shell::ZSH);
        $targetDir = $this->completionRuntime->getInstallationDirectory();

        Filesystem\create_directory($targetDir);
        Filesystem\write_file($targetPath, '# completion script');

        $this->console
            ->call('completion:uninstall --shell=zsh')
            ->assertSee('Uninstalling zsh completions')
            ->deny()
            ->assertSee('Uninstallation cancelled')
            ->assertCancelled();

        $this->assertTrue(Filesystem\is_file($targetPath));

        Filesystem\delete_file($targetPath);
    }
}
