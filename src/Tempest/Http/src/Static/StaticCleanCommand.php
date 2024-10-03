<?php

declare(strict_types=1);

namespace Tempest\Http\Static;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\Middleware\CautionMiddleware;
use Tempest\Console\Middleware\ForceMiddleware;
use Tempest\Core\Kernel;

final readonly class StaticCleanCommand
{
    use HasConsole;

    public function __construct(
        private Console $console,
        private Kernel $kernel,
    ) {
    }

    #[ConsoleCommand(
        name: 'static:clean',
        middleware: [ForceMiddleware::class, CautionMiddleware::class]
    )]
    public function __invoke(): void
    {
        /** @var SplFileInfo[] $files */
        $files = [];

        $directoryIterator = new RecursiveDirectoryIterator($this->kernel->root . '/public');

        /** @var SplFileInfo $file */
        foreach (new RecursiveIteratorIterator($directoryIterator) as $file) {
            if ($file->getExtension() === 'html') {
                $files[] = $file;
            }
        }

        foreach ($files as $file) {
            unlink($file->getPathname());

            $pathName = str_replace('\\', '/', $file->getPathname());

            $this->writeln("- <u>{$pathName}</u> removed");
        }

        $this->success('Done');
    }
}
