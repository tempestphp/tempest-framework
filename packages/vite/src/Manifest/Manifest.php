<?php

declare(strict_types=1);

namespace Tempest\Vite\Manifest;

use Tempest\Support\Arr\ImmutableArray;

use function Tempest\Support\arr;

final readonly class Manifest
{
    private function __construct(
        /**
         * All chunks in the manifest.
         * @var ImmutableArray<int,Chunk>
         */
        public ImmutableArray $chunks,
        /**
         * Chunks that are entrypoints.
         * @var ImmutableArray<int,Chunk>
         */
        public ImmutableArray $entrypoints,
    ) {}

    public static function fromArray(array $chunks): self
    {
        $chunks = arr($chunks)->map(fn (array $value) => Chunk::fromArray($value));

        return new self(
            chunks: $chunks,
            entrypoints: $chunks->filter(fn (Chunk $entry) => $entry->isEntry),
        );
    }
}
