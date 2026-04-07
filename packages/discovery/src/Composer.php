<?php

declare(strict_types=1);

namespace Tempest\Discovery;

use Tempest\Process\ProcessExecutor;
use Tempest\Support\Arr;
use Tempest\Support\Filesystem;
use Tempest\Support\Namespace\Psr4Namespace;
use Tempest\Support\Path;
use Tempest\Support\Str;

final class Composer
{
    /** @var array<Psr4Namespace> */
    public array $namespaces;

    /** @var array<Psr4Namespace> */
    public array $devNamespaces;

    public ?Psr4Namespace $mainNamespace = null;

    private string $composerPath;

    private array $composer;

    public function __construct(
        private readonly string $root,
        private ?ProcessExecutor $executor = null,
    ) {}

    public function load(): self
    {
        $this->composerPath = Path\normalize($this->root, 'composer.json');
        $this->composer = $this->loadComposerFile($this->composerPath);
        $this->namespaces = $this->resolvePsr4Namespaces('autoload.psr-4');

        foreach ($this->namespaces as $namespace) {
            if (! Str\starts_with(Str\ensure_ends_with($namespace->path, '/'), needles: ['app/', 'src/', 'source/', 'lib/'])) {
                continue;
            }

            $this->mainNamespace = $namespace;

            break;
        }

        if (! isset($this->mainNamespace) && count($this->namespaces)) {
            $this->mainNamespace = $this->namespaces[0];
        }

        $this->namespaces = new Arr\ImmutableArray([$this->mainNamespace, ...$this->namespaces])
            ->filter()
            ->unique(fn (Psr4Namespace $ns) => "{$ns->namespace}:{$ns->path}")
            ->toArray();

        $this->devNamespaces = $this->resolvePsr4Namespaces('autoload-dev.psr-4');

        return $this;
    }

    public function setMainNamespace(Psr4Namespace $namespace): self
    {
        $this->mainNamespace = $namespace;

        return $this;
    }

    public function setNamespaces(Psr4Namespace|array $namespaces): self
    {
        $this->namespaces = Arr\wrap($namespaces);

        return $this;
    }

    public function setProcessExecutor(ProcessExecutor $executor): self
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
        Filesystem\write_json($this->composerPath, $this->composer);

        return $this;
    }

    public function executeUpdate(): self
    {
        if ($this->executor instanceof ProcessExecutor) {
            $this->executor->run('composer up');
        } else {
            exec('composer update');
        }

        return $this;
    }

    private function loadComposerFile(string $path): array
    {
        if (! Filesystem\is_file($path)) {
            throw new ComposerJsonCouldNotBeLocated("Could not locate {$path}");
        }

        return Filesystem\read_json($path);
    }

    /** @return array<Psr4Namespace> */
    private function resolvePsr4Namespaces(string $path): array
    {
        return new Arr\ImmutableArray($this->composer)
            ->get($path, default: new Arr\ImmutableArray())
            ->flatMap(fn (string|iterable $paths, string $namespace) => Arr\map(Arr\wrap($paths), fn (string $path) => new Psr4Namespace($namespace, $path)))
            ->sortByCallback(fn (Psr4Namespace $ns1, Psr4Namespace $ns2) => strlen($ns1->path) <=> strlen($ns2->path))
            ->values()
            ->toArray();
    }
}
