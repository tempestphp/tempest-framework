<?php

namespace Tempest\Support\Paginator;

use JsonSerializable;

/**
 * @template T
 */
final class SimplePaginatedData implements JsonSerializable
{
    /**
     * @param array<T> $data
     */
    public function __construct(
        public array $data,
        public int $currentPage,
        public int $itemsPerPage,
        public int $offset,
        public int $limit,
        public bool $hasNext,
        public bool $hasPrevious,
        public ?int $nextPage,
        public ?int $previousPage,
    ) {}

    public int $count {
        get => count($this->data);
    }

    public bool $isEmpty {
        get => $this->count === 0;
    }

    public bool $isNotEmpty {
        get => ! $this->isEmpty;
    }

    /**
     * @template U
     *
     * @param callable(T): U $callback
     *
     * @return SimplePaginatedData<U>
     */
    public function map(callable $callback): self
    {
        return new self(
            data: array_map($callback, $this->data),
            currentPage: $this->currentPage,
            itemsPerPage: $this->itemsPerPage,
            offset: $this->offset,
            limit: $this->limit,
            hasNext: $this->hasNext,
            hasPrevious: $this->hasPrevious,
            nextPage: $this->nextPage,
            previousPage: $this->previousPage,
        );
    }

    /**
     * @return array{
     *     data: array<T>,
     *     pagination: array{
     *         current_page: int,
     *         items_per_page: int,
     *         offset: int,
     *         limit: int,
     *         has_next: bool,
     *         has_previous: bool,
     *         next_page: ?int,
     *         previous_page: ?int,
     *         count: int
     *     }
     * }
     */
    public function toArray(): array
    {
        return [
            'data' => $this->data,
            'pagination' => [
                'current_page' => $this->currentPage,
                'items_per_page' => $this->itemsPerPage,
                'offset' => $this->offset,
                'limit' => $this->limit,
                'has_next' => $this->hasNext,
                'has_previous' => $this->hasPrevious,
                'next_page' => $this->nextPage,
                'previous_page' => $this->previousPage,
                'count' => $this->count,
            ],
        ];
    }

    /**
     * @return array{
     *     data: array<T>,
     *     pagination: array{
     *         current_page: int,
     *         items_per_page: int,
     *         offset: int,
     *         limit: int,
     *         has_next: bool,
     *         has_previous: bool,
     *         next_page: ?int,
     *         previous_page: ?int,
     *         count: int
     *     }
     * }
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
