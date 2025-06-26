<?php

declare(strict_types=1);

namespace Tempest\Core;

use Tempest\Support\Namespace\Psr4Namespace;

use function Tempest\Support\arr;
use function Tempest\Support\Arr\wrap;
use function Tempest\Support\Path\normalize;
use function Tempest\Support\Str\ensure_ends_with;
use function Tempest\Support\Str\starts_with;

final class Composer
{
    /** @var array<Psr4Namespace> */
    public array $namespaces;

    public ?Psr4Namespace $mainNamespace = null;

    private string $composerPath;

    private array $composer;

    public function __construct(
        private readonly string $root,
        private ShellExecutor $executor,
    ) {}

    public function load(): self
    {
        $this->composerPath = normalize($this->root, 'composer.json');
        $this->composer = $this->loadComposerFile($this->composerPath);
        $this->namespaces = arr($this->composer)
            ->get('autoload.psr-4', default: arr())
            ->map(fn (string $path, string $namespace) => new Psr4Namespace($namespace, $path))
            ->sortByCallback(fn (Psr4Namespace $ns1, Psr4Namespace $ns2) => strlen($ns1->path) <=> strlen($ns2->path))
            ->values()
            ->toArray();

        foreach ($this->namespaces as $namespace) {
            if (starts_with(ensure_ends_with($namespace->path, '/'), ['app/', 'src/', 'source/', 'lib/'])) {
                $this->mainNamespace = $namespace;

                break;
            }
        }

        if (! isset($this->mainNamespace) && count($this->namespaces)) {
            $this->mainNamespace = $this->namespaces[0];
        }

        $this->namespaces = arr([
            $this->mainNamespace,
            ...$this->namespaces,
        ])
            ->filter()
            ->unique(fn (Psr4Namespace $ns) => $ns->namespace)
            ->toArray();

        return $this;
    }

    public function setMainNamespace(Psr4Namespace $namespace): self
    {
        $this->mainNamespace = $namespace;

        return $this;
    }

    public function setNamespaces(Psr4Namespace|array $namespaces): self
    {
        $this->namespaces = wrap($namespaces);

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
            throw new ComposerJsonCouldNotBeLocated('Could not locate composer.json.');
        }

        return json_decode(file_get_contents($path), associative: true);
    }
}
