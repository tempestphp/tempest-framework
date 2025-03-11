<?php

declare(strict_types=1);

namespace Tempest\Vite\TagsResolver;

use Tempest\Vite\Exceptions\FileSystemEntrypointNotFoundException;
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
    ) {
    }

    public function resolveTags(array $entrypoints): array
    {
        return arr($entrypoints)
            ->map(function (string $entrypoint) {
                if (! file_exists(root_path($entrypoint))) {
                    throw new FileSystemEntrypointNotFoundException($entrypoint);
                }

                return $this->createDevelopmentTag($this->fileToAssetPath($entrypoint));
            })
            ->prepend($this->createDevelopmentTag(self::CLIENT_SCRIPT_PATH))
            ->toArray();
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
}
