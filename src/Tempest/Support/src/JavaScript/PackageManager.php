<?php

declare(strict_types=1);

namespace Tempest\Support\JavaScript;

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
        foreach (PackageManager::cases() as $packageManager) {
            foreach ($packageManager->getLockFiles() as $lockFile) {
                if (file_exists($cwd . '/' . $lockFile)) {
                    return $packageManager;
                }
            }
        }

        return null;
    }
}
