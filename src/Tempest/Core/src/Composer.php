<?php

declare(strict_types=1);

namespace Tempest\Core;

use function Tempest\Support\arr;
use Tempest\Support\PathHelper;

final readonly class Composer
{
    /** @var array<ComposerNamespace> */
    public array $namespaces;

    public ?ComposerNamespace $mainNamespace;

    public array $composer;

    public function __construct(
        string $root,
    ) {
        $composerFilePath = PathHelper::make($root, 'composer.json');

        $this->composer = $this->loadComposerFile($composerFilePath);
        $this->namespaces = arr($this->composer)
            ->get('autoload.psr-4', default: arr())
            ->map(fn (string $path, string $namespace) => new ComposerNamespace($namespace, $path))
            ->values()
            ->toArray();

        foreach ($this->namespaces as $namespace) {
            if (str_starts_with($namespace->path, 'app/') || str_starts_with($namespace->path, 'src/')) {
                $this->mainNamespace = $namespace;

                break;
            }
        }

        if (! isset($this->mainNamespace) && count($this->namespaces)) {
            $this->mainNamespace = $this->namespaces[0];
        }

        if (! isset($this->mainNamespace)) {
            throw new KernelException("Tempest requires at least one PSR-4 namespace to be defined in composer.json.");
        }
    }

    private function loadComposerFile(string $path): array
    {
        if (! file_exists($path)) {
            throw new KernelException("Could not locate composer.json.");
        }

        return json_decode(file_get_contents($path), associative: true);
    }
}
