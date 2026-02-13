<?php

declare(strict_types=1);

namespace Tempest\Console\Actions;

use RuntimeException;
use Tempest\Console\CompletionRuntime;
use Tempest\Support\Filesystem;

final readonly class EnsureCompletionHelperBinary
{
    public function __invoke(): string
    {
        $binaryPath = CompletionRuntime::getHelperBinaryPath();
        $bundledBinaryPath = CompletionRuntime::getBundledHelperBinaryPath();

        if (! Filesystem\is_file($bundledBinaryPath)) {
            $platform = CompletionRuntime::getHelperBinaryPlatform();

            throw new RuntimeException("Completion helper binary for platform `{$platform}` was not found: {$bundledBinaryPath}");
        }

        $mustCopyBinary = true;

        if (Filesystem\is_file($binaryPath)) {
            if (! Filesystem\is_executable($binaryPath)) {
                chmod($binaryPath, 0o755);
            }

            if ($this->hasMatchingHash($binaryPath, $bundledBinaryPath)) {
                $mustCopyBinary = false;
            }

            if (! $mustCopyBinary && Filesystem\is_executable($binaryPath)) {
                return $binaryPath;
            }
        }

        if ($mustCopyBinary) {
            Filesystem\ensure_directory_exists(dirname($binaryPath));
            Filesystem\copy_file($bundledBinaryPath, $binaryPath, overwrite: true);
        }

        chmod($binaryPath, 0o755);

        if (Filesystem\is_executable($binaryPath)) {
            return $binaryPath;
        }

        throw new RuntimeException("Completion helper binary could not be made executable: {$binaryPath}");
    }

    private function hasMatchingHash(string $runtimeBinaryPath, string $bundledBinaryPath): bool
    {
        if (! Filesystem\is_readable($runtimeBinaryPath) || ! Filesystem\is_readable($bundledBinaryPath)) {
            return false;
        }

        $runtimeHash = hash_file('xxh128', $runtimeBinaryPath);
        $bundledHash = hash_file('xxh128', $bundledBinaryPath);

        if (! is_string($runtimeHash) || ! is_string($bundledHash)) {
            return false;
        }

        return hash_equals($runtimeHash, $bundledHash);
    }
}
