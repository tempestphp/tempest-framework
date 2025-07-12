<?php

declare(strict_types=1);

namespace Tempest\Vite\TagsResolver;

use Tempest\Support\Filesystem;
use Tempest\Vite\Exceptions\FileSystemEntrypointWasNotFoundException;
use Tempest\Vite\TagCompiler\TagCompiler;
use Tempest\Vite\ViteBridgeFile;

use function Tempest\root_path;
use function Tempest\Support\arr;
use function Tempest\Support\str;

final readonly class DevelopmentTagsResolver implements TagsResolver
{
    public const string CLIENT_SCRIPT_PATH = '@vite/client';

    public function __construct(
        private ViteBridgeFile $bridgeFile,
        private TagCompiler $tagCompiler,
    ) {}

    public function resolveTags(array $entrypoints): array
    {
        $tags = arr($entrypoints)
            ->map(function (string $entrypoint) {
                if (! Filesystem\exists($entrypoint) && ! Filesystem\exists(root_path($entrypoint))) {
                    throw new FileSystemEntrypointWasNotFoundException($entrypoint);
                }

                return $this->createDevelopmentTag($this->fileToAssetPath($entrypoint));
            })
            ->prepend($this->createDevelopmentTag(self::CLIENT_SCRIPT_PATH));

        if ($this->bridgeFile->needsReactRefresh) {
            $tags = $tags->prepend($this->createReactRefreshTag());
        }

        return $tags->toArray();
    }

    private function createDevelopmentTag(string $path): string
    {
        $url = str($this->bridgeFile->url)
            ->finish('/')
            ->append(str($path)->replaceStart('/', ''))
            ->toString();

        if (preg_match('/\.(css|less|sass|scss|styl|stylus|pcss|postcss)$/', $path) === 1) {
            return $this->tagCompiler->compileStyleTag($url);
        }

        return $this->tagCompiler->compileScriptTag($url);
    }

    private function fileToAssetPath(string $file): string
    {
        return str($file)
            ->when(
                condition: fn ($file) => $file->startsWith('./'),
                callback: fn ($file) => str(realpath(root_path($file->toString()))),
            )
            ->replace('\\', '/') // `realpath` makes slashes backwards, so replacements below wouldn't work
            ->replaceStart(root_path('public'), '')
            ->replaceStart(root_path(), '')
            ->toString();
    }

    private function createReactRefreshTag(): string
    {
        return <<<HTML
            <script type="module">
                import RefreshRuntime from '{$this->bridgeFile->url}/@react-refresh';
                RefreshRuntime.injectIntoGlobalHook(window);
                window.\$RefreshReg$ = () => {};
                window.\$RefreshSig$ = () => (type) => type;
                window.__vite_plugin_react_preamble_installed__ = true;
            </script>
        HTML;
    }
}
