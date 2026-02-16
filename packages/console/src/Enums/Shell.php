<?php

declare(strict_types=1);

namespace Tempest\Console\Enums;

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

    public function getCompletionFilename(): string
    {
        return match ($this) {
            self::ZSH => 'tempest.zsh',
            self::BASH => 'tempest.bash',
        };
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
}
