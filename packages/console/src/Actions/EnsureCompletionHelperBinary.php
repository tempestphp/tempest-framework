<?php

declare(strict_types=1);

namespace Tempest\Console\Actions;

use RuntimeException;
use Tempest\Console\CompletionRuntime;
use Tempest\Support\Filesystem;

use function Tempest\Support\box;

final readonly class EnsureCompletionHelperBinary
{
    public function __invoke(bool $update = false): string
    {
        $binaryPath = CompletionRuntime::getHelperBinaryPath();

        if (!$update && Filesystem\is_file($binaryPath) && Filesystem\is_executable($binaryPath)) {
            return $binaryPath;
        }

        $downloadUrl = CompletionRuntime::getHelperBinaryDownloadUrl();
        $binaryContents = $this->downloadBinary($downloadUrl);

        Filesystem\ensure_directory_exists(dirname($binaryPath));
        Filesystem\write_file($binaryPath, $binaryContents);

        chmod($binaryPath, 0o755);

        if (Filesystem\is_executable($binaryPath)) {
            return $binaryPath;
        }

        throw new RuntimeException("Downloaded completion helper binary could not be made executable: {$binaryPath}");
    }

    private function downloadBinary(string $downloadUrl): string
    {
        $context = stream_context_create([
            'http' => [
                'follow_location' => 1,
                'max_redirects' => 10,
                'timeout' => 30,
                'user_agent' => 'tempest-completion-installer',
            ],
        ]);

        [$contents, $errorMessage] = box(static fn (): false|string => file_get_contents($downloadUrl, false, $context));

        if (! is_string($contents) || $contents === '') {
            $platform = CompletionRuntime::getHelperBinaryPlatform();

            throw new RuntimeException("Failed to download completion helper binary for platform `{$platform}` from {$downloadUrl}. {$errorMessage}");
        }

        return $contents;
    }
}
