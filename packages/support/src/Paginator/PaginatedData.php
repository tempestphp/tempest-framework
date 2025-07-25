<?php

namespace Tempest\Support\Paginator;

use JsonSerializable;

/**
 * @template T
 */
final class PaginatedData implements JsonSerializable
{
    /**
     * @param array<T> $data
     */
    public function __construct(
        public array $data,
        public int $currentPage,
        public int $totalPages,
        public int $totalItems,
        public int $itemsPerPage,
        public int $offset,
        public int $limit,
        public bool $hasNext,
        public bool $hasPrevious,
        public ?int $nextPage,
        public ?int $previousPage,
        public array $pageRange,
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
     * @param callable(mixed): U $callback
     * @return PaginatedData<U>
     */
    public function map(callable $callback): self
    {
        return new self(
            data: array_map($callback, $this->data),
            currentPage: $this->currentPage,
            totalPages: $this->totalPages,
            totalItems: $this->totalItems,
            itemsPerPage: $this->itemsPerPage,
            offset: $this->offset,
            limit: $this->limit,
            hasNext: $this->hasNext,
            hasPrevious: $this->hasPrevious,
            nextPage: $this->nextPage,
            previousPage: $this->previousPage,
            pageRange: $this->pageRange,
        );
    }

    public function toArray(): array
    {
        return [
            'data' => $this->data,
            'pagination' => [
                'current_page' => $this->currentPage,
                'total_pages' => $this->totalPages,
                'total_items' => $this->totalItems,
                'items_per_page' => $this->itemsPerPage,
                'offset' => $this->offset,
                'limit' => $this->limit,
                'has_next' => $this->hasNext,
                'has_previous' => $this->hasPrevious,
                'next_page' => $this->nextPage,
                'previous_page' => $this->previousPage,
                'page_range' => $this->pageRange,
                'count' => $this->count,
            ],
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
