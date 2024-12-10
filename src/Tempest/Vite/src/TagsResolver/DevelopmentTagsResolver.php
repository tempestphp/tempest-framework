<?php

declare(strict_types=1);

namespace Tempest\Vite\TagsResolver;

use Tempest\Vite\BridgeFile;
use Tempest\Vite\TagCompiler\TagCompiler;
use function Tempest\root_path;
use function Tempest\Support\arr;
use function Tempest\Support\str;

final class DevelopmentTagsResolver implements TagsResolver
{
    public const string CLIENT_SCRIPT_PATH = '@vite/client';

    public function __construct(
        private readonly BridgeFile $bridgeFile,
        private readonly TagCompiler $tagCompiler,
    ) {
    }

    public function resolveTags(array $entrypoints): array
    {
        return arr($entrypoints)
            ->map(fn (string $file) => $this->createDevelopmentTag($this->fileToAssetPath($file)))
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
            ->replaceStart(root_path('public'), '')
            ->replaceStart(root_path(), '')
            ->toString();
    }
}
