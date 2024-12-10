<?php

declare(strict_types=1);

namespace Tempest\Core;

use function Tempest\path;
use function Tempest\Support\arr;

final class Composer
{
    /** @var array<ComposerNamespace> */
    public array $namespaces;

    public ?ComposerNamespace $mainNamespace = null;

    private string $composerPath;

    private array $composer;

    public function __construct(
        private readonly string $root,
        private ShellExecutor $executor,
    ) {
    }

    public function load(): self
    {
        $this->composerPath = path($this->root, 'composer.json')->toString();
        $this->composer = $this->loadComposerFile($this->composerPath);
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

        return $this;
    }

    public function setMainNamespace(ComposerNamespace $namespace): self
    {
        $this->mainNamespace = $namespace;

        return $this;
    }

    public function setShellExecutor(ShellExecutor $executor): self
    {
        $this->executor = $executor;

        return $this;
    }

    public function addNamespace(string $namespace, string $path): self
    {
        $path = str_replace($this->root, '.', $path);

        $this->composer['autoload']['psr-4'][$namespace] = $path;

        return $this;
    }

    public function save(): self
    {
        file_put_contents($this->composerPath, json_encode($this->composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $this;
    }

    public function executeUpdate(): self
    {
        $this->executor->execute('composer up');

        return $this;
    }

    private function loadComposerFile(string $path): array
    {
        if (! file_exists($path)) {
            throw new KernelException('Could not locate composer.json.');
        }

        return json_decode(file_get_contents($path), associative: true);
    }
}
