<?php

namespace Tempest\Storage;

use League\Flysystem\DirectoryListing as FlysystemDirectoryListing;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Support\Arr\MutableArray;
use Traversable;

/**
 * @template T
 */
final class DirectoryListing extends FlysystemDirectoryListing
{
    /**
     * @param iterable<T> $listing
     */
    public function __construct(
        private iterable $listing,
    ) {}

    /**
     * @param callable(T): bool $filter
     *
     * @return static<T>
     */
    public function filter(callable $filter): static
    {
        return new static(parent::filter($filter));
    }

    /**
     * @template R
     *
     * @param callable(T): R $mapper
     *
     * @return static<R>
     */
    public function map(callable $mapper): static
    {
        return new static(parent::map($mapper));
    }

    /**
     * @return static<T>
     */
    public function sortByPath(): static
    {
        return new static(parent::sortByPath());
    }

    /**
     * @return MutableArray<T>
     */
    public function toMutableArray(): MutableArray
    {
        return new MutableArray($this->toArray());
    }

    /**
     * @return ImmutableArray<T>
     */
    public function toImmutableArray(): ImmutableArray
    {
        return new ImmutableArray($this->toArray());
    }

    /**
     * @return T[]
     */
    public function toArray(): array
    {
        return ($this->listing instanceof Traversable)
            ? iterator_to_array($this->listing, false)
            : $this->listing;
    }
}
