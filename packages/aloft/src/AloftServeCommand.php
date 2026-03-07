<?php

declare(strict_types=1);

namespace Tempest\Aloft;

use Tempest\Console\Console;
use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;

use function Tempest\root_path;
use function Tempest\Support\Filesystem\exists;

final readonly class AloftServeCommand
{
    use HasConsole;

    public bool $stubsPublished;

    public ?string $assumedVariant;

    public string $remotePath;

    public function __construct()
    {
        $testPath = root_path('docker') . DIRECTORY_SEPARATOR . 'Dockerfile.';

        $this->assumedVariant = match (true) {
            exists("{$testPath}latest") => 'latest',
            exists("{$testPath}debug") => 'debug',
            default => null,
        };

        $this->stubsPublished = $this->assumedVariant !== null;

        $this->remotePath = 'PLACE.HOLD.ER/';
    }

    #[ConsoleCommand(
        name: 'aloft:build',
        description: 'Build the Aloft Docker image locally, and publish the stub files if not already present.',
    )]
    public function build(
        #[ConsoleArgument(
            description: 'The build variant to use.',
        )]
        string $requestedVariant = '',
        #[ConsoleArgument(
            name: 'repository',
            description: 'Space-separated list of extra extensions to include in the build.',
        )]
        ?string $repository = null,
    ): void {
        $variant = $requestedVariant === '' ? $this->assumedVariant ?? 'debug' : $requestedVariant;
        $repo = $repository ?? ($this->stubsPublished ? '' : $this->remotePath);

        // TODO: Catch local development paths from composer.json and insert them as volumes

        $runImage = "{$repo}tempestphp/aloft:{$variant}";

        if ($this->confirm("Do you want to start dev server from {$runImage}?", default: true)) {
            $this->console->info('Okay, starting, use ctrl-c to exit when finished');
            if ($this->stubsPublished === true && ! ($repository ?? null === 'remote')) {
                $this->console->info('Stubs are published, and you are using the local repository, therefore ensure that you run aloft:build before using aloft:serve');
            }
            passthru(
                "docker run --rm -it -p 80:8000 -p 443:8443 -p 443:8443/udp \
                -v "
                . root_path()
                . ":/app \
                -v "
                . root_path('.frankenpest/data')
                . ":/data \
                -v "
                . root_path('.frankenpest/config')
                . ":/config \
                {$runImage}",
            );
        }
    }
}
