<?php

declare(strict_types=1);

namespace Tempest\Console\Enums;

use Tempest\Console\CompletionRuntime;

enum Shell: string
{
    case ZSH = 'zsh';
    case BASH = 'bash';

    public static function detect(): ?self
    {
        $shell = getenv('SHELL');

        if ($shell === false) {
            return null;
        }

        return match (true) {
            str_contains($shell, 'zsh') => self::ZSH,
            str_contains($shell, 'bash') => self::BASH,
            default => null,
        };
    }

    public function getCompletionsDirectory(): string
    {
        return CompletionRuntime::getInstallationDirectory();
    }

    public function getCompletionFilename(): string
    {
        return match ($this) {
            self::ZSH => 'tempest.zsh',
            self::BASH => 'tempest.bash',
        };
    }

    public function getInstalledCompletionPath(): string
    {
        return $this->getCompletionsDirectory() . '/' . $this->getCompletionFilename();
    }

    public function getSourceFilename(): string
    {
        return match ($this) {
            self::ZSH => 'completion.zsh',
            self::BASH => 'completion.bash',
        };
    }

    public function getRcFile(): string
    {
        $home = $_SERVER['HOME'] ?? getenv('HOME') ?: '';

        return match ($this) {
            self::ZSH => $home . '/.zshrc',
            self::BASH => $home . '/.bashrc',
        };
    }

    /**
     * @return string[]
     */
    public function getPostInstallInstructions(): array
    {
        $rcFile = $this->getRcFile();
        $installedPath = $this->getInstalledCompletionPath();

        return match ($this) {
            self::ZSH => [
                "Add this line to {$rcFile} and restart your terminal:",
                '',
                "  source {$installedPath}",
            ],
            self::BASH => [
                "Add this line to {$rcFile} and restart your terminal:",
                '',
                "  source {$installedPath}",
            ],
        };
    }
}
