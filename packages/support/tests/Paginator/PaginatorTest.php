<?php

namespace Tempest\Support\Tests\Paginator;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Paginator\Exceptions\ArgumentWasInvalid;
use Tempest\Support\Paginator\PaginatedData;
use Tempest\Support\Paginator\Paginator;

final class PaginatorTest extends TestCase
{
    private function createPaginator(int $totalItems = 100, int $itemsPerPage = 10, int $currentPage = 1, int $maxLinks = 5): Paginator
    {
        return new Paginator($totalItems, $itemsPerPage, $currentPage, $maxLinks);
    }

    #[Test]
    public function test_it_calculates_basic_pagination_properties(): void
    {
        $paginator = $this->createPaginator(totalItems: 100, itemsPerPage: 10, currentPage: 5);

        $this->assertSame(5, $paginator->currentPage);
        $this->assertSame(10, $paginator->totalPages);
        $this->assertSame(100, $paginator->totalItems);
        $this->assertSame(10, $paginator->itemsPerPage);
        $this->assertSame(40, $paginator->offset);
        $this->assertSame(10, $paginator->limit);
    }

    #[Test]
    public function test_it_handles_navigation_properties(): void
    {
        $paginator = $this->createPaginator(totalItems: 100, itemsPerPage: 10, currentPage: 5);

        $this->assertTrue($paginator->hasNext);
        $this->assertTrue($paginator->hasPrevious);
        $this->assertSame(6, $paginator->nextPage);
        $this->assertSame(4, $paginator->previousPage);
        $this->assertSame(1, $paginator->firstPage);
        $this->assertSame(10, $paginator->lastPage);
    }

    #[Test]
    public function test_it_handles_first_page_navigation(): void
    {
        $paginator = $this->createPaginator(currentPage: 1);

        $this->assertFalse($paginator->hasPrevious);
        $this->assertNull($paginator->previousPage);
        $this->assertTrue($paginator->hasNext);
        $this->assertSame(2, $paginator->nextPage);
    }

    #[Test]
    public function test_it_handles_last_page_navigation(): void
    {
        $paginator = $this->createPaginator(totalItems: 100, itemsPerPage: 10, currentPage: 10);

        $this->assertFalse($paginator->hasNext);
        $this->assertNull($paginator->nextPage);
        $this->assertTrue($paginator->hasPrevious);
        $this->assertSame(9, $paginator->previousPage);
    }

    #[Test]
    public function test_it_handles_single_page_scenario(): void
    {
        $paginator = $this->createPaginator(totalItems: 5, itemsPerPage: 10, currentPage: 1);

        $this->assertSame(1, $paginator->totalPages);
        $this->assertFalse($paginator->hasNext);
        $this->assertFalse($paginator->hasPrevious);
        $this->assertNull($paginator->nextPage);
        $this->assertNull($paginator->previousPage);
    }

    #[Test]
    public function test_it_handles_empty_results(): void
    {
        $paginator = $this->createPaginator(totalItems: 0);

        $this->assertSame(1, $paginator->totalPages);
        $this->assertSame(1, $paginator->currentPage);
        $this->assertSame(0, $paginator->offset);
        $this->assertSame(1, $paginator->firstPage);
        $this->assertSame(1, $paginator->lastPage);
    }

    #[Test]
    #[DataProvider('pageRangeProvider')]
    public function test_it_calculates_page_ranges_correctly(int $totalItems, int $itemsPerPage, int $currentPage, int $maxLinks, array $expectedRange): void
    {
        $paginator = $this->createPaginator($totalItems, $itemsPerPage, $currentPage, $maxLinks);

        $this->assertEquals($expectedRange, $paginator->pageRange);
    }

    public static function pageRangeProvider(): array
    {
        return [
            'few pages, show all' => [50, 10, 3, 10, [1, 2, 3, 4, 5]],
            'many pages, beginning' => [1000, 10, 2, 5, [1, 2, 3, 4, 5]],
            'many pages, middle' => [1000, 10, 50, 5, [48, 49, 50, 51, 52]],
            'many pages, near end' => [1000, 10, 98, 5, [96, 97, 98, 99, 100]],
            'exact max links' => [50, 10, 3, 5, [1, 2, 3, 4, 5]],
            'single page' => [5, 10, 1, 5, [1]],
        ];
    }

    #[Test]
    public function test_it_constrains_current_page_to_valid_range(): void
    {
        $paginator = $this->createPaginator(totalItems: 50, itemsPerPage: 10, currentPage: 100);

        $this->assertSame(5, $paginator->currentPage); // Should be capped at max page

        $this->expectException(ArgumentWasInvalid::class);
        $this->createPaginator(currentPage: 0);
    }

    #[Test]
    public function test_it_creates_paginated_data_with_array(): void
    {
        $paginator = $this->createPaginator(totalItems: 100, itemsPerPage: 10, currentPage: 5);
        $data = ['item1', 'item2', 'item3'];

        $paginatedData = $paginator->paginate($data);

        $this->assertInstanceOf(PaginatedData::class, $paginatedData);
        $this->assertSame($data, $paginatedData->data);
        $this->assertSame(5, $paginatedData->currentPage);
        $this->assertSame(10, $paginatedData->totalPages);
        $this->assertSame(100, $paginatedData->totalItems);
    }

    #[Test]
    public function test_it_creates_paginated_data_with_callback(): void
    {
        $paginator = $this->createPaginator(totalItems: 100, itemsPerPage: 10, currentPage: 3);

        $dataFetcher = function (int $_limit, int $offset): array {
            return ["item_{$offset}_1", "item_{$offset}_2"];
        };

        $paginatedData = $paginator->paginateWith($dataFetcher);

        $this->assertSame(['item_20_1', 'item_20_2'], $paginatedData->data);
        $this->assertSame(3, $paginatedData->currentPage);
    }

    #[Test]
    public function test_it_creates_immutable_copies_with_different_page(): void
    {
        $original = $this->createPaginator(currentPage: 1);
        $modified = $original->withPage(5);

        $this->assertNotSame($original, $modified);
        $this->assertSame(1, $original->currentPage);
        $this->assertSame(5, $modified->currentPage);
    }

    #[Test]
    public function test_it_creates_immutable_copies_with_different_items_per_page(): void
    {
        $original = $this->createPaginator(itemsPerPage: 10);
        $modified = $original->withItemsPerPage(25);

        $this->assertNotSame($original, $modified);
        $this->assertSame(10, $original->itemsPerPage);
        $this->assertSame(25, $modified->itemsPerPage);
    }

    #[Test]
    public function test_it_handles_fractional_pages_correctly(): void
    {
        // 105 items with 10 per page should give 11 pages
        $paginator = $this->createPaginator(totalItems: 105, itemsPerPage: 10);

        $this->assertSame(11, $paginator->totalPages);
    }

    #[Test]
    public function test_it_calculates_offset_correctly_for_different_pages(): void
    {
        $paginator = $this->createPaginator(totalItems: 100, itemsPerPage: 10);

        $this->assertSame(0, $paginator->offset);
        $this->assertSame(10, $paginator->withPage(2)->offset);
        $this->assertSame(20, $paginator->withPage(3)->offset);
    }
}
