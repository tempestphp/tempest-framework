<?php

namespace Tempest\Support\Paginator;

use Tempest\Support\Paginator\Exceptions\ArgumentWasInvalid;

final class SimplePaginator
{
    public function __construct(
        private(set) int $itemsPerPage = 20,
        private(set) int $currentPage = 1,
    ) {
        if ($this->itemsPerPage <= 0) {
            throw new ArgumentWasInvalid('Items per page must be positive');
        }

        if ($this->currentPage <= 0) {
            throw new ArgumentWasInvalid('Current page must be positive');
        }
    }

    public int $offset {
        get => ($this->currentPage - 1) * $this->itemsPerPage;
    }

    /**
     * One additional item is requested to determine whether
     * the next page exists.
     */
    public int $limit {
        get => $this->itemsPerPage + 1;
    }

    public bool $hasPrevious {
        get => $this->currentPage > 1;
    }

    public ?int $previousPage {
        get => $this->hasPrevious ? $this->currentPage - 1 : null;
    }

    public function withPage(int $page): self
    {
        return new self(
            itemsPerPage: $this->itemsPerPage,
            currentPage: $page,
        );
    }

    public function withItemsPerPage(int $itemsPerPage): self
    {
        return new self(
            itemsPerPage: $itemsPerPage,
            currentPage: $this->currentPage,
        );
    }

    /**
     * Creates simple paginated data with the provided items.
     *
     * The array may contain one additional item, which is used
     * to determine whether the next page exists.
     *
     * @template T
     * @param array<T> $data
     * @return SimplePaginatedData<T>
     */
    public function paginate(array $data): SimplePaginatedData
    {
        $hasNext = count($data) > $this->itemsPerPage;

        if ($hasNext) {
            array_pop($data);
        }

        return new SimplePaginatedData(
            data: $data,
            currentPage: $this->currentPage,
            itemsPerPage: $this->itemsPerPage,
            offset: $this->offset,
            limit: $this->itemsPerPage,
            hasNext: $hasNext,
            hasPrevious: $this->hasPrevious,
            nextPage: $hasNext ? $this->currentPage + 1 : null,
            previousPage: $this->previousPage,
        );
    }

    /**
     * Creates simple paginated data from a callable that fetches data.
     *
     * @template T
     * @param callable(int $limit, int $offset): array<T> $callback
     * @return SimplePaginatedData<T>
     */
    public function paginateWith(callable $callback): SimplePaginatedData
    {
        return $this->paginate(
            $callback($this->limit, $this->offset),
        );
    }
}
