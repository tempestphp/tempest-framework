<?php

declare(strict_types=1);

namespace Tempest\Core;

use Tempest\Support\PathHelper;

final readonly class Composer
{
    public array $namespaces;

    public string $mainNamespace;

    public string $mainNamespacePath;

    public function __construct(
        string $root,
    ) {
        $composerFilePath = PathHelper::make($root, 'composer.json');
        $composer = $this->loadComposerFile($composerFilePath);

        $this->namespaces = $composer['autoload']['psr-4'] ?? [];

        foreach ($this->namespaces as $namespace => $path) {
            if (str_starts_with($path, 'app/') || str_starts_with($path, 'src/')) {
                $this->mainNamespace = $namespace;
                $this->mainNamespacePath = $path;

                break;
            }
        }

        if (! $this->mainNamespace) {
            $this->mainNamespace = array_key_first($this->namespaces);
            $this->mainNamespacePath = $this->namespaces[$this->mainNamespace];
        }

        if (! $this->mainNamespace) {
            throw new KernelException("Tempest requires at least one PSR-4 namespace to be defined in composer.json.");
        }
    }

    private function loadComposerFile(string $path): array
    {
        if (! file_exists($path)) {
            throw new KernelException("Could not locate composer.json.");
        }

        return json_decode(file_get_contents($path), true);
    }
}
