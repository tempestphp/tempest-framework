<?php

declare(strict_types=1);

namespace Tempest\Vite\TagsResolver;

use Tempest\Vite\Exceptions\EntrypointNotFoundException;
use Tempest\Vite\Manifest\Chunk;
use Tempest\Vite\Manifest\Manifest;
use Tempest\Vite\TagCompiler\TagCompiler;
use Tempest\Vite\ViteConfig;
use function Tempest\root_path;
use function Tempest\Support\arr;
use function Tempest\Support\str;

final class ManifestTagsResolver implements TagsResolver
{
    public function __construct(
        private readonly ViteConfig $viteConfig,
        private readonly TagCompiler $tagCompiler,
        private readonly Manifest $manifest,
    ) {
    }

    public function resolveTags(array $entrypoints): array
    {
        return arr($entrypoints)
            ->flatMap(function (string $entrypoint) {
                $path = $this->fileToAssetPath($entrypoint);

                if (! $chunk = $this->manifest->chunks->get($path)) {
                    throw new EntrypointNotFoundException($entrypoint);
                }

                return $this->resolveEntryPointTags($chunk);
            })
            ->toArray();
    }

    private function resolveEntryPointTags(Chunk $entrypoint): array
    {
        return arr()
            ->push(...$this->getPreloadTags($entrypoint))
            ->push(...$this->getStyleTags($entrypoint))
            ->push($this->resolveChunkTag($entrypoint))
            ->unique()
            ->toArray();
    }

    private function resolveChunkTag(Chunk $chunk): string
    {
        if (str_ends_with($chunk->file, '.css')) {
            return $this->tagCompiler->compileStyleTag($this->getChunkPath($chunk), $chunk);
        }

        if ($chunk->isEntry) {
            return $this->tagCompiler->compileScriptTag($this->getChunkPath($chunk), $chunk);
        }

        return $this->tagCompiler->compilePreloadTag($this->getChunkPath($chunk), $chunk);
    }

    private function getStyleTags(Chunk $chunk): array
    {
        $seenFiles = [];

        $getStyleChunks = function (Chunk $chunk) use (&$seenFiles, &$getStyleChunks) {
            $styleChunks = [];

            foreach ($chunk->imports as $importFile) {
                if (isset($seenFiles[$importFile])) {
                    continue;
                }

                $seenFiles[$importFile] = true;
                $importChunk = $this->manifest->chunks[$importFile] ?? null;

                if ($importChunk) {
                    $styleChunks = array_merge(
                        $styleChunks,
                        $getStyleChunks($importChunk),
                    );
                }
            }

            $styleChunks = array_merge(
                $styleChunks,
                array_map(fn (string $path) => ['file' => $path], $chunk->css),
            );

            return $styleChunks;
        };

        $styleChunks = $getStyleChunks($chunk);

        return arr($styleChunks)
            ->map(fn (array $styleChunk) => $this->tagCompiler->compileStyleTag($this->getAssetPath($styleChunk['file'])))
            ->unique()
            ->toArray();
    }

    private function getPreloadTags(Chunk $chunk): array
    {
        $seenFiles = [];

        $getPreloadChunks = function (Chunk $chunk) use (&$seenFiles, &$getPreloadChunks) {
            $preloadChunks = [];

            foreach ($chunk->imports as $importFile) {
                if (isset($seenFiles[$importFile])) {
                    continue;
                }

                $seenFiles[$importFile] = true;
                $importChunk = $this->manifest->chunks[$importFile] ?? null;

                if ($importChunk) {
                    $preloadChunks = array_merge(
                        $preloadChunks,
                        $getPreloadChunks($importChunk),
                    );

                    $preloadChunks[] = $importChunk;
                }
            }

            return $preloadChunks;
        };

        $preloadChunks = $getPreloadChunks($chunk);

        return arr($preloadChunks)
            ->map(fn (Chunk $preloadChunk) => $this->tagCompiler->compilePreloadTag($this->getChunkPath($preloadChunk), $preloadChunk))
            ->unique()
            ->toArray();
    }

    private function getChunkPath(Chunk $chunk): string
    {
        return $this->getAssetPath($chunk->file);
    }

    private function getAssetPath(string $path): string
    {
        return $this->viteConfig->build->buildDirectory . '/' . $path;
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
            ->replaceStart('/', '')
            ->toString();
    }
}
