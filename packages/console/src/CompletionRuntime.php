<?php

declare(strict_types=1);

namespace Tempest\Console;

use RuntimeException;
use Symfony\Component\Filesystem\Path;
use Tempest\Console\Enums\Shell;
use Tempest\Container\Singleton;

use function Tempest\internal_storage_path;
use function Tempest\Support\path;

#[Singleton]
final readonly class CompletionRuntime
{
    public function getInstallationDirectory(): string
    {
        return Path::canonicalize(path($this->getProfileDirectory(), '.tempest', 'completion')->toString());
    }

    public function getMetadataPath(): string
    {
        return internal_storage_path('completion', 'commands.json');
    }

    public function isSupportedPlatform(?string $osFamily = null): bool
    {
        $osFamily ??= PHP_OS_FAMILY;

        return match ($osFamily) {
            'Darwin', 'Linux' => true,
            default => false,
        };
    }

    public function getUnsupportedPlatformMessage(): string
    {
        return 'Completion commands are supported on Linux and macOS. Use WSL if you are on Windows.';
    }

    public function getInstalledCompletionPath(Shell $shell): string
    {
        return $this->getInstallationDirectory() . '/' . $shell->getCompletionFilename();
    }

    /**
     * @return string[]
     */
    public function getPostInstallInstructions(Shell $shell): array
    {
        $rcFile = $shell->getRcFile();
        $installedPath = $this->getInstalledCompletionPath($shell);

        return match ($shell) {
            Shell::ZSH => [
                "Add this line to {$rcFile} and restart your terminal:",
                '',
                "  source {$installedPath}",
            ],
            Shell::BASH => [
                "Add this line to {$rcFile} and restart your terminal:",
                '',
                "  source {$installedPath}",
            ],
        };
    }

    private function getProfileDirectory(): string
    {
        $profileDirectory = $_SERVER['HOME'] ?? $_ENV['HOME'] ?? getenv('HOME') ?: null;

        if ($profileDirectory === null) {
            $profileDirectory = $_SERVER['USERPROFILE'] ?? $_ENV['USERPROFILE'] ?? getenv('USERPROFILE') ?: null;
        }

        if ($profileDirectory === null && getenv('HOMEDRIVE') !== false && getenv('HOMEPATH') !== false) {
            $profileDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
        }

        if ($profileDirectory === null) {
            throw new RuntimeException('Could not determine user profile directory for completions.');
        }

        return $profileDirectory;
    }
}
