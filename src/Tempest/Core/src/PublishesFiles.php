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

    private array $publishedFiles = [];

    private array $publishedClasses = [];

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

        $this->publishedFiles[] = $destination;

        if ($callback !== null) {
            $callback($source, $destination);
        }

        $this->success("{$destination} created");
    }

    public function publishImports(): void
    {
        foreach ($this->publishedFiles as $file) {
            $contents = str(file_get_contents($file));

            foreach ($this->publishedClasses as $old => $new) {
                $contents = $contents->replace($old, $new);
            }

            file_put_contents($file, $contents);
        }
    }

    private function updateClass(string $destination): void
    {
        try {
            $class = new ClassManipulator($destination);
        } catch (InvalidStateException) {
            return;
        }

        $namespace = str($destination)
            ->replaceStart(rtrim(src_path(), '/'), src_namespace())
            ->replaceEnd('.php', '')
            ->replace('/', '\\')
            ->explode('\\')
            ->pop($value)
            ->implode('\\')
            ->toString();

        $oldClassName = $class->getClassName();

        $class
            ->setNamespace($namespace)
            ->removeClassAttribute(DoNotDiscover::class)
            ->save($destination);

        $this->publishedClasses[$oldClassName] = $class->getClassName();
    }
}
