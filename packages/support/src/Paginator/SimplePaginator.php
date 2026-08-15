<?php

namespace Tempest\Support\Paginator;

use Tempest\Support\Paginator\Exceptions\CurrentPageWasInvalid;
use Tempest\Support\Paginator\Exceptions\ItemsPerPageWasInvalid;

final class SimplePaginator
{
    public function __construct(
        private(set) int $itemsPerPage = 20,
        private(set) int $currentPage = 1,
    ) {
        if ($this->itemsPerPage <= 0 || $this->itemsPerPage === PHP_INT_MAX) {
            throw new ItemsPerPageWasInvalid($this->itemsPerPage);
        }

        $maximumCurrentPage = min(PHP_INT_MAX - 2, intdiv(PHP_INT_MAX, $this->itemsPerPage)) + 1;

        if ($this->currentPage <= 0 || $this->currentPage > $maximumCurrentPage) {
            throw new CurrentPageWasInvalid(
                currentPage: $this->currentPage,
                itemsPerPage: $this->itemsPerPage,
                maximumCurrentPage: $maximumCurrentPage,
            );
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
     * Any items beyond the configured page size are used to determine
     * whether the next page exists and are omitted from the result.
     *
     * @template T
     * @param array<T> $data
     * @return SimplePaginatedData<T>
     */
    public function paginate(array $data): SimplePaginatedData
    {
        $hasNext = count($data) > $this->itemsPerPage;
        $data = array_slice($data, 0, $this->itemsPerPage);

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
