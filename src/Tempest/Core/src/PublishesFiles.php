<?php

declare(strict_types=1);

namespace Tempest\Core;

use Closure;
use Tempest\Console\HasConsole;

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

        if ($callback !== null) {
            $callback($source, $destination);
        }

        $this->success("{$destination} created");
    }
}
