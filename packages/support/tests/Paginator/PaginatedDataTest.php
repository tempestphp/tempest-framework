<?php

namespace Tempest\Support\Tests\Paginator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Paginator\PaginatedData;

final class PaginatedDataTest extends TestCase
{
    private function createSamplePaginatedData(array $data = ['item1', 'item2', 'item3']): PaginatedData
    {
        return new PaginatedData(
            data: $data,
            currentPage: 2,
            totalPages: 5,
            totalItems: 100,
            itemsPerPage: 20,
            offset: 20,
            limit: 20,
            hasNext: true,
            hasPrevious: true,
            nextPage: 3,
            previousPage: 1,
            pageRange: [1, 2, 3, 4, 5],
        );
    }

    #[Test]
    public function it_stores_data_and_pagination_info(): void
    {
        $data = ['item1', 'item2', 'item3'];
        $paginatedData = $this->createSamplePaginatedData($data);

        $this->assertSame($data, $paginatedData->data);
        $this->assertSame(2, $paginatedData->currentPage);
        $this->assertSame(5, $paginatedData->totalPages);
        $this->assertSame(100, $paginatedData->totalItems);
        $this->assertTrue($paginatedData->hasNext);
        $this->assertTrue($paginatedData->hasPrevious);
    }

    #[Test]
    public function it_calculates_count_property(): void
    {
        $paginatedData = $this->createSamplePaginatedData(['a', 'b', 'c', 'd']);

        $this->assertSame(4, $paginatedData->count);
    }

    #[Test]
    public function it_checks_empty_status(): void
    {
        $emptyData = $this->createSamplePaginatedData([]);
        $nonEmptyData = $this->createSamplePaginatedData(['item']);

        $this->assertTrue($emptyData->isEmpty);
        $this->assertFalse($emptyData->isNotEmpty);

        $this->assertFalse($nonEmptyData->isEmpty);
        $this->assertTrue($nonEmptyData->isNotEmpty);
    }

    #[Test]
    public function it_maps_data_while_preserving_pagination(): void
    {
        $original = $this->createSamplePaginatedData([1, 2, 3]);
        $mapped = $original->map(fn ($x) => $x * 2);

        $this->assertSame([2, 4, 6], $mapped->data);
        $this->assertSame($original->currentPage, $mapped->currentPage);
        $this->assertSame($original->totalPages, $mapped->totalPages);
        $this->assertSame($original->totalItems, $mapped->totalItems);
    }

    #[Test]
    public function it_converts_to_array(): void
    {
        $paginatedData = $this->createSamplePaginatedData(['a', 'b']);
        $array = $paginatedData->toArray();

        $expected = [
            'data' => ['a', 'b'],
            'pagination' => [
                'current_page' => 2,
                'total_pages' => 5,
                'total_items' => 100,
                'items_per_page' => 20,
                'offset' => 20,
                'limit' => 20,
                'has_next' => true,
                'has_previous' => true,
                'next_page' => 3,
                'previous_page' => 1,
                'page_range' => [1, 2, 3, 4, 5],
                'count' => 2,
            ],
        ];

        $this->assertEquals($expected, $array);
    }
}
