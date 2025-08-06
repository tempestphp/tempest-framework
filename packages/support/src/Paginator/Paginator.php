<?php

namespace Tempest\Support\Paginator;

use Tempest\Support\Paginator\Exceptions\ArgumentWasInvalid;

final class Paginator
{
    public function __construct(
        private(set) int $totalItems,
        private(set) int $itemsPerPage = 20,
        private(set) int $currentPage = 1,
        private(set) int $maxLinks = 10,
    ) {
        if ($this->totalItems < 0) {
            throw new ArgumentWasInvalid('Total items cannot be negative');
        }

        if ($this->itemsPerPage <= 0) {
            throw new ArgumentWasInvalid('Items per page must be positive');
        }

        if ($this->currentPage <= 0) {
            throw new ArgumentWasInvalid('Current page must be positive');
        }

        if ($this->maxLinks <= 0) {
            throw new ArgumentWasInvalid('Max links must be positive');
        }

        $this->currentPage = min(max(1, $this->currentPage), $this->totalPages);
    }

    public int $totalPages {
        get => max(1, (int) ceil($this->totalItems / $this->itemsPerPage));
    }

    public int $offset {
        get => ($this->currentPage - 1) * $this->itemsPerPage;
    }

    public int $limit {
        get => $this->itemsPerPage;
    }

    public bool $hasNext {
        get => $this->currentPage < $this->totalPages;
    }

    public bool $hasPrevious {
        get => $this->currentPage > 1;
    }

    public ?int $nextPage {
        get => $this->hasNext ? ($this->currentPage + 1) : null;
    }

    public ?int $previousPage {
        get => $this->hasPrevious ? ($this->currentPage - 1) : null;
    }

    public ?int $firstPage {
        get => $this->totalPages > 0 ? 1 : null;
    }

    public ?int $lastPage {
        get => $this->totalPages > 0 ? $this->totalPages : null;
    }

    public array $pageRange {
        get => $this->calculatePageRange();
    }

    public function withPage(int $page): self
    {
        return new self(
            totalItems: $this->totalItems,
            itemsPerPage: $this->itemsPerPage,
            currentPage: $page,
            maxLinks: $this->maxLinks,
        );
    }

    public function withItemsPerPage(int $itemsPerPage): self
    {
        return new self(
            totalItems: $this->totalItems,
            itemsPerPage: $itemsPerPage,
            currentPage: $this->currentPage,
            maxLinks: $this->maxLinks,
        );
    }

    /**
     * Creates paginated data with the provided items.
     *
     * @template T
     * @param array<T> $data
     * @return PaginatedData<T>
     */
    public function paginate(array $data): PaginatedData
    {
        return new PaginatedData(
            data: $data,
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

    /**
     * Creates paginated data from a callable that fetches data.
     *
     * @template T
     * @param callable(int $limit, int $offset): array<T> $callback
     * @return PaginatedData<T>
     */
    public function paginateWith(callable $callback): PaginatedData
    {
        return $this->paginate($callback($this->limit, $this->offset));
    }

    private function calculatePageRange(): array
    {
        if ($this->totalPages <= $this->maxLinks) {
            return range(1, $this->totalPages);
        }

        $half = (int) floor($this->maxLinks / 2);
        $start = max(1, $this->currentPage - $half);
        $end = min($this->totalPages, ($start + $this->maxLinks) - 1);

        if ((($end - $start) + 1) < $this->maxLinks) {
            $start = max(1, ($end - $this->maxLinks) + 1);
        }

        return range($start, $end);
    }
}
