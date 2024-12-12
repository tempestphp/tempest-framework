<?php

declare(strict_types=1);

namespace Tempest\Vite\Manifest;

use Tempest\Support\ArrayHelper;
use function Tempest\Support\arr;

final class Manifest
{
    private function __construct(
        /**
         * All chunks in the manifest.
         * @var ArrayHelper<int,Chunk>
         */
        public readonly ArrayHelper $chunks,
        /**
         * Chunks that are entrypoints.
         * @var ArrayHelper<int,Chunk>
         */
        public readonly ArrayHelper $entrypoints,
    ) {
    }

    public static function fromArray(array $chunks): self
    {
        $chunks = arr($chunks)->map(fn (array $value) => Chunk::fromArray($value));

        return new self(
            chunks: $chunks,
            entrypoints: $chunks->filter(fn (Chunk $entry) => $entry->isEntry),
        );
    }
}
