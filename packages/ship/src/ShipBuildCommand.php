<?php

declare(strict_types=1);

namespace Tempest\Ship;

use Tempest\Console\Console;
use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;

use function Tempest\root_path;
use function Tempest\Support\Filesystem\exists;

final readonly class ShipBuildCommand
{
    use HasConsole;

    public bool $stubsPublished;

    public ?string $assumedVariant;

    public function __construct()
    {
        $testPath = root_path('docker') . DIRECTORY_SEPARATOR . 'Dockerfile.';

        $this->assumedVariant = match (true) {
            exists("{$testPath}latest") => 'latest',
            exists("{$testPath}debug") => 'debug',
            default => null,
        };

        $this->stubsPublished = $this->assumedVariant !== null;
    }

    #[ConsoleCommand(
        name: 'ship:build',
        description: 'Build the Aloft Docker image locally, and publish the stub files if not already present.',
    )]
    public function build(
        #[ConsoleArgument(
            description: 'The build variant to use.',
        )]
        string $requestedVariant = '',
        #[ConsoleArgument(
            name: 'with-php-extensions',
            description: 'Space-separated list of extra extensions to include in the build.',
        )]
        ?string $withPhpExtensions = null,
        #[ConsoleArgument(
            name: 'with-frankenphp',
            description: 'FrankenPHP version to pass as a build ARG.',
        )]
        ?string $withFrankenphp = null,
        #[ConsoleArgument(
            name: 'with-php',
            description: 'PHP version to pass as a build ARG.',
        )]
        ?string $withPhp = null,
    ): void {
        $variant = $requestedVariant === '' ? $this->assumedVariant ?? 'debug' : $requestedVariant;

        $buildArgs = implode('', array_filter([
            $withFrankenphp !== null ? " --build-arg FRANKENPHP_VERSION=\"{$withFrankenphp}\"" : null,
            $withPhp !== null ? " --build-arg PHP_VERSION=\"{$withPhp}\"" : null,
            $withPhpExtensions !== null ? " --build-arg PHP_EXTRA_EXTENSIONS=\"{$withPhpExtensions}\"" : null,
        ]));

        $buildPath = ($this->stubsPublished ? root_path('docker') : dirname(__DIR__) . DIRECTORY_SEPARATOR . 'stubs') . DIRECTORY_SEPARATOR;
        $buildFile = "{$buildPath}Dockerfile.{$variant} -t tempestphp/ship:{$variant}";

        if ($this->confirm("Do you want to build tempestphp/ship:{$variant}?", default: false)) {
            $this->console->info('Okay, attempting build');
            passthru("docker build -f {$buildFile}{$buildArgs} {$buildPath}");
        }
    }
}
