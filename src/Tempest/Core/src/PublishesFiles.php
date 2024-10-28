<?php

declare(strict_types=1);

namespace Tempest\Core;

use Closure;
use Nette\InvalidStateException;
use Tempest\Console\HasConsole;
use Tempest\Generation\ClassManipulator;
use function Tempest\src_namespace;
use function Tempest\src_path;
use function Tempest\Support\str;

trait PublishesFiles
{
    use HasConsole;

    /**
     * @param Closure(string $source, string $destination): void|null $callback
     */
    public function publish(
        string $source,
        string $destination,
        ?Closure $callback = null,
    ): void {
        if (file_exists($destination)) {
            if (! $this->confirm(
                question: "{$destination} already exists Do you want to overwrite it?",
            )) {
                return;
            }
        } else {
            if (! $this->confirm(
                question: "Do you want to create {$destination}?",
                default: true,
            )) {
                $this->writeln('Skipped');

                return;
            }
        }

        $dir = pathinfo($destination, PATHINFO_DIRNAME);

        if (! is_dir($dir)) {
            mkdir($dir, recursive: true);
        }

        copy($source, $destination);

        $this->updateClass($destination);

        if ($callback !== null) {
            $callback($source, $destination);
        }

        $this->success("{$destination} created");
    }

    private function updateClass(string $destination): void
    {
        try {
            $class = new ClassManipulator($destination);
        } catch (InvalidStateException) {
            return;
        }

        $namespace = str($destination)
            ->replaceStart(src_path(), src_namespace())
            ->replaceEnd('.php', '')
            ->replace('/', '\\')
            ->explode('\\')
            ->pop($value)
            ->implode('\\')
            ->toString();

        $class
            ->setNamespace($namespace)
            ->removeClassAttribute(DoNotDiscover::class)
            ->save($destination);
    }
}
