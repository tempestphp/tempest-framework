<?php

declare(strict_types=1);

namespace Tempest\Aloft;

use Tempest\Console\Console;
use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Support\Arr\MutableArray;
use Tempest\Support\Str\ImmutableString;

use function Tempest\root_path;
use function Tempest\Support\arr;
use function Tempest\Support\Filesystem\copy_file;
use function Tempest\Support\Filesystem\exists;
use function Tempest\Support\str;

final readonly class AloftPublishCommand
{
    use HasConsole;

    #[ConsoleCommand(
        name: 'aloft:publish',
        description: 'Publish the Aloft Docker stubs, for the debug image.',
        aliases: ['aloft:publish:debug', 'aloft:publish:dev'],
    )]
    public function publish(string $variant = 'debug'): void
    {
        $srcPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR;

        $dstPath = root_path('docker') . DIRECTORY_SEPARATOR;

        $files = arr([
            '.dockerignore' => '.dockerignore',
            'Caddyfile' => 'Caddyfile',
            "Dockerfile.{$variant}" => "Dockerfile.{$variant}",
        ])
            ->each(
                function (string $dstFile, string $srcFile) use ($srcPath, $dstPath) {
                    copy_file(
                        source: $srcPath . $srcFile,
                        destination: $dstPath . $dstFile,
                    );
                },
            );

        // copy_file will throw a runtime exception if this fails, so write a success
        $this->console->success("Stub files copied to {$dstPath}");
    }

    #[ConsoleCommand(
        name: 'aloft:publish:latest',
        description: 'Publish the Aloft Docker stubs, for the distroless image.',
        aliases: ['aloft:publish:distroless', 'aloft:publish:prod'],
    )]
    public function publishLatest(): void
    {
        $this->publish('latest');
    }
}
