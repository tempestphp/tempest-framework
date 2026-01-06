<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Commands;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Console\Enums\Shell;
use Tempest\Support\Filesystem;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class CompletionUninstallCommandTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function uninstall_with_explicit_shell_flag(): void
    {
        $targetPath = Shell::ZSH->getInstalledCompletionPath();
        $targetDir = Shell::ZSH->getCompletionsDirectory();

        Filesystem\create_directory($targetDir);
        Filesystem\write_file($targetPath, '# completion script');

        $this->console
            ->call('completion:uninstall --shell=zsh --force')
            ->assertSee('Removed completion script:')
            ->assertSee('_tempest')
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
        $targetPath = Shell::ZSH->getInstalledCompletionPath();

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
        $targetPath = Shell::BASH->getInstalledCompletionPath();
        $targetDir = Shell::BASH->getCompletionsDirectory();

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
        $targetPath = Shell::ZSH->getInstalledCompletionPath();
        $targetDir = Shell::ZSH->getCompletionsDirectory();

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
