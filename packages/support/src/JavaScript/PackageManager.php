<?php

declare(strict_types=1);

namespace Tempest\Support\JavaScript;

use Tempest\Support\Filesystem;

/**
 * Represents the major package managers in the JavaScript ecosystem.
 * This enum is backed for serialization purposes.
 */
enum PackageManager: string
{
    case BUN = 'bun';
    case PNPM = 'pnpm';
    case YARN = 'yarn';
    case NPM = 'npm';

    public function getLockFiles(): array
    {
        return match ($this) {
            self::BUN => ['bun.lock', 'bun.lockb'],
            self::NPM => ['package-lock.json'],
            self::YARN => ['yarn.lock'],
            self::PNPM => ['pnpm-lock.yaml'],
        };
    }

    public function getBinaryName(): string
    {
        return match ($this) {
            self::BUN => 'bun',
            self::NPM => 'npm',
            self::YARN => 'yarn',
            self::PNPM => 'pnpm',
        };
    }

    public function getRunCommand(string $script): string
    {
        return (
            $this->getBinaryName() .
            ' ' . match ($this) {
                self::BUN => $script,
                self::NPM => "run {$script}",
                self::YARN => $script,
                self::PNPM => $script,
            }
        );
    }

    public function getInstallCommand(): string
    {
        return match ($this) {
            self::BUN => 'install',
            self::NPM => 'install',
            self::YARN => '',
            self::PNPM => 'install',
        };
    }

    public static function detect(string $cwd): ?self
    {
        return array_find(
            array: PackageManager::cases(),
            callback: fn ($packageManager) => array_any($packageManager->getLockFiles(), fn ($lockFile) => Filesystem\is_file($cwd . '/' . $lockFile)),
        );
    }
}
