<?php

declare(strict_types=1);

namespace Tempest\Router\Static;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\Middleware\CautionMiddleware;
use Tempest\Console\Middleware\ForceMiddleware;
use Tempest\Core\Kernel;
use Tempest\EventBus\EventBus;

final readonly class StaticCleanCommand
{
    use HasConsole;

    public function __construct(
        private Kernel $kernel,
        private EventBus $eventBus,
    ) {}

    #[ConsoleCommand(
        name: 'static:clean',
        description: 'Removes statically generated pages',
        middleware: [ForceMiddleware::class, CautionMiddleware::class],
    )]
    public function __invoke(): void
    {
        $this->console->header('Cleaning static pages');

        $directoryIterator = new RecursiveDirectoryIterator($this->kernel->root . '/public');
        $directoryIterator->setFlags(FilesystemIterator::SKIP_DOTS);

        $removed = 0;

        $this->eventBus->listen(function (StaticPageRemoved $event) use (&$removed): void {
            $removed++;
            $this->keyValue("<style='fg-gray'>{$event->path}</style>", "<style='fg-green'>REMOVED</style>");
        });

        $this->removeFiles($directoryIterator);
        $this->removeEmptyDirectories($directoryIterator);

        $this->keyValue('Static pages removed', "<style='fg-green'>{$removed}</style>");
    }

    private function removeFiles(RecursiveDirectoryIterator $directoryIterator): void
    {
        /** @var SplFileInfo[] $files */
        $files = [];

        /** @var SplFileInfo $file */
        foreach (new RecursiveIteratorIterator($directoryIterator) as $file) {
            if ($file->getExtension() === 'html') {
                $files[] = $file;
            }
        }

        foreach ($files as $file) {
            unlink($file->getPathname());

            $pathName = str_replace('\\', '/', $file->getPathname());

            $this->eventBus->dispatch(new StaticPageRemoved($pathName));
        }
    }

    private function removeEmptyDirectories(RecursiveDirectoryIterator $directoryIterator): void
    {
        /** @var SplFileInfo $file */
        foreach (new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::CHILD_FIRST) as $file) {
            if (! $file->isDir()) {
                continue;
            }

            if (count(glob($file->getPathname() . '/*')) > 0) {
                continue;
            }

            rmdir($file->getPathname());
        }
    }
}
