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

    public function getCompletionsDirectory(): string
    {
        $home = $_SERVER['HOME'] ?? getenv('HOME') ?: '';

        return match ($this) {
            self::ZSH => $home . '/.zsh/completions',
            self::BASH => $home . '/.bash_completion.d',
        };
    }

    public function getCompletionFilename(): string
    {
        return match ($this) {
            self::ZSH => '_tempest',
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
        return match ($this) {
            self::ZSH => [
                'Add the completions directory to your fpath in ~/.zshrc:',
                '',
                '  fpath=(~/.zsh/completions $fpath)',
                '',
                'Then reload completions:',
                '',
                '  autoload -Uz compinit && compinit',
                '',
                'Or restart your terminal.',
            ],
            self::BASH => [
                'Source the completion file in your ~/.bashrc:',
                '',
                '  source ~/.bash_completion.d/tempest.bash',
                '',
                'Or restart your terminal.',
            ],
        };
    }
}
