<?php

declare(strict_types=1);

namespace Tempest\Console\Enums;

enum Shell: string
{
    case ZSH = 'zsh';
    case BASH = 'bash';

    public function rcFile(): string
    {
        $home = $this->getHomeDirectory();

        return match ($this) {
            self::ZSH => $home . '/.zshrc',
            self::BASH => $home . '/.bashrc',
        };
    }

    public function completionsDirectory(): string
    {
        $home = $this->getHomeDirectory();

        return match ($this) {
            self::ZSH => $home . '/.zsh/completions',
            self::BASH => $home . '/.local/share/bash-completion/completions',
        };
    }

    public function completionScriptName(): string
    {
        return match ($this) {
            self::ZSH => '_tempest',
            self::BASH => 'tempest',
        };
    }

    public function completionSourceFile(): string
    {
        return match ($this) {
            self::ZSH => 'complete.zsh',
            self::BASH => 'complete.bash',
        };
    }

    public static function detect(): ?self
    {
        return match (self::getEnv('SHELL') |> basename(...)) {
            'zsh' => self::ZSH,
            'bash' => self::BASH,
            default => null,
        };
    }

    private function getHomeDirectory(): string
    {
        return self::getEnv('HOME');
    }

    private static function getEnv(string $name): string
    {
        if (is_string($_SERVER[$name] ?? null)) {
            return $_SERVER[$name];
        }

        return getenv($name) ?: '';
    }
}
