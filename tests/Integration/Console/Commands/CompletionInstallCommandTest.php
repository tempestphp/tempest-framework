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
final class CompletionInstallCommandTest extends FrameworkIntegrationTestCase
{
    private ?string $installedFile = null;

    protected function tearDown(): void
    {
        if ($this->installedFile !== null && Filesystem\is_file($this->installedFile)) {
            Filesystem\delete_file($this->installedFile);
            $this->installedFile = null;
        }

        parent::tearDown();
    }

    #[Test]
    public function install_with_explicit_shell_flag(): void
    {
        $this->installedFile = Shell::ZSH->getInstalledCompletionPath();

        $this->console
            ->call('completion:install --shell=zsh --force')
            ->assertSee('Installed completion script to:')
            ->assertSee('_tempest')
            ->assertSuccess();
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
        $this->installedFile = Shell::ZSH->getInstalledCompletionPath();

        $this->console
            ->call('completion:install --shell=zsh --force')
            ->assertSee('fpath=')
            ->assertSee('compinit')
            ->assertSuccess();
    }

    #[Test]
    public function install_shows_post_install_instructions_for_bash(): void
    {
        $this->installedFile = Shell::BASH->getInstalledCompletionPath();

        $this->console
            ->call('completion:install --shell=bash --force')
            ->assertSee('source')
            ->assertSee('tempest.bash')
            ->assertSuccess();
    }

    #[Test]
    public function install_cancelled_when_user_denies_confirmation(): void
    {
        $this->console
            ->call('completion:install --shell=zsh')
            ->assertSee('Installing zsh completions')
            ->deny()
            ->assertSee('Installation cancelled')
            ->assertCancelled();
    }

    #[Test]
    public function install_creates_directory_if_not_exists(): void
    {
        $targetDir = Shell::ZSH->getCompletionsDirectory();
        $dirExisted = Filesystem\is_directory($targetDir);

        $this->installedFile = Shell::ZSH->getInstalledCompletionPath();

        $result = $this->console
            ->call('completion:install --shell=zsh --force');

        if (! $dirExisted) {
            $result->assertSee('Created directory:');
        }

        $result->assertSuccess();
    }

    #[Test]
    public function install_asks_for_overwrite_when_file_exists(): void
    {
        $targetPath = Shell::ZSH->getInstalledCompletionPath();
        $targetDir = Shell::ZSH->getCompletionsDirectory();

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
}
