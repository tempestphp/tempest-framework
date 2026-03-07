<?php

declare(strict_types=1);

namespace Tempest\Console\Enums;

enum Shell: string
{
    case ZSH = 'zsh';
    case BASH = 'bash';
    case FISH = 'fish';

    public static function detect(): ?self
    {
        $shell = getenv('SHELL');

        if ($shell === false) {
            return null;
        }

        return match (true) {
            str_contains($shell, 'zsh') => self::ZSH,
            str_contains($shell, 'bash') => self::BASH,
            str_contains($shell, 'fish') => self::FISH,
            default => null,
        };
    }

    public function getCompletionFilename(): string
    {
        return match ($this) {
            self::ZSH => 'tempest.zsh',
            self::BASH => 'tempest.bash',
            self::FISH => 'tempest.fish',
        };
    }

    public function getSourceFilename(): string
    {
        return match ($this) {
            self::ZSH => 'completion.zsh',
            self::BASH => 'completion.bash',
            self::FISH => 'completion.fish',
        };
    }

    public function supportsCompletionDescriptions(): bool
    {
        return match ($this) {
            self::ZSH, self::FISH => true,
            self::BASH => false,
        };
    }

    public function getRcFile(): string
    {
        $home = $_SERVER['HOME'] ?? getenv('HOME') ?: '';

        return match ($this) {
            self::ZSH => $home . '/.zshrc',
            self::BASH => $home . '/.bashrc',
            self::FISH => $home . '/.config/fish/config.fish',
        };
    }
}
