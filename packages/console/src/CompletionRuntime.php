<?php

declare(strict_types=1);

namespace Tempest\Console;

use RuntimeException;
use Symfony\Component\Filesystem\Path;

use function Tempest\internal_storage_path;
use function Tempest\Support\path;

final class CompletionRuntime
{
    public const string HELPER_PATH_PLACEHOLDER = '__TEMPEST_COMPLETION_BINARY__';
    public const string METADATA_PATH_PLACEHOLDER = '__TEMPEST_COMPLETION_METADATA__';
    public const string RELEASE_DOWNLOAD_BASE_URL = 'https://github.com/tempestphp/tempest-framework/releases/download';

    public static function getInstallationDirectory(): string
    {
        return Path::canonicalize(path(self::getProfileDirectory(), '.tempest', 'completion')->toString());
    }

    public static function getDirectory(): string
    {
        return internal_storage_path('completion');
    }

    public static function getMetadataPath(): string
    {
        return internal_storage_path('completion', 'commands.json');
    }

    public static function getHelperBinaryPath(): string
    {
        return internal_storage_path('completion', self::getHelperBinaryFilename());
    }

    public static function getHelperBinaryAssetFilename(): string
    {
        return self::getHelperBinaryFilename() . '_' . str_replace('-', '_', self::getHelperBinaryPlatform());
    }

    public static function getHelperBinaryReleaseTag(): string
    {
        $releaseTag = self::resolveInstalledReleaseVersion();

        if (str_contains($releaseTag, 'dev')) {
            throw new RuntimeException("Completion helper binaries are only available for tagged releases. Current version is `{$releaseTag}`.");
        }

        if (str_starts_with($releaseTag, 'v')) {
            return $releaseTag;
        }

        return "v{$releaseTag}";
    }

    public static function getHelperBinaryDownloadUrl(): string
    {
        return sprintf(
            self::RELEASE_DOWNLOAD_BASE_URL . '/%s/%s',
            self::getHelperBinaryReleaseTag(),
            self::getHelperBinaryAssetFilename(),
        );
    }

    public static function isSupportedPlatform(?string $osFamily = null): bool
    {
        return match ($osFamily ?? PHP_OS_FAMILY) {
            'Darwin', 'Linux' => true,
            default => false,
        };
    }

    public static function getUnsupportedPlatformMessage(): string
    {
        return 'Completion commands are supported on Linux and macOS. Use WSL if you are on Windows.';
    }

    public static function getHelperBinaryPlatform(): string
    {
        $os = match (PHP_OS_FAMILY) {
            'Darwin' => 'darwin',
            'Linux' => 'linux',
            default => strtolower(PHP_OS_FAMILY),
        };

        $architecture = match (strtolower((string) php_uname('m'))) {
            'amd64', 'x86_64' => 'x86_64',
            'aarch64', 'arm64' => 'arm64',
            default => strtolower((string) php_uname('m')),
        };

        return "{$os}-{$architecture}";
    }

    public static function getHelperBinaryFilename(): string
    {
        return 'tempest-complete';
    }

    private static function resolveInstalledReleaseVersion(): string
    {
        if (! class_exists(\Composer\InstalledVersions::class)) {
            throw new RuntimeException('Unable to determine the installed Tempest version to download completion helper binaries.');
        }

        foreach (['tempest/framework', 'tempest/console'] as $package) {
            if (! \Composer\InstalledVersions::isInstalled($package)) {
                continue;
            }

            $version = \Composer\InstalledVersions::getPrettyVersion($package);

            if (is_string($version) && $version !== '') {
                return $version;
            }
        }

        throw new RuntimeException('Unable to determine the installed Tempest version to download completion helper binaries.');
    }

    private static function getProfileDirectory(): string
    {
        $profileDirectory = $_SERVER['HOME'] ?? $_ENV['HOME'] ?? getenv('HOME') ?: null;

        if ($profileDirectory === null || $profileDirectory === '') {
            $profileDirectory = $_SERVER['USERPROFILE'] ?? $_ENV['USERPROFILE'] ?? getenv('USERPROFILE') ?: null;
        }

        if (($profileDirectory === null || $profileDirectory === '') && getenv('HOMEDRIVE') !== false && getenv('HOMEPATH') !== false) {
            $profileDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
        }

        if ($profileDirectory === null || $profileDirectory === '') {
            throw new RuntimeException('Could not determine user profile directory for completions.');
        }

        return $profileDirectory;
    }
}
