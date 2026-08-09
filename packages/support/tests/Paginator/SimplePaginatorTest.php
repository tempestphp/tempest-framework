<?php

declare(strict_types=1);

namespace Tempest\Support\Tests\Paginator;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Paginator\Exceptions\ArgumentWasInvalid;
use Tempest\Support\Paginator\SimplePaginatedData;
use Tempest\Support\Paginator\SimplePaginator;

final class SimplePaginatorTest extends TestCase
{
    #[Test]
    public function exactly_one_page_does_not_have_a_next_page(): void
    {
        $result = new SimplePaginator(itemsPerPage: 3)->paginate(['a', 'b', 'c']);

        $this->assertSame(['a', 'b', 'c'], $result->data);
        $this->assertFalse($result->hasNext);
        $this->assertNull($result->nextPage);
        $this->assertSame(3, $result->count);
    }

    #[Test]
    public function an_additional_item_marks_the_next_page_and_is_removed(): void
    {
        $result = new SimplePaginator(itemsPerPage: 3)->paginate(['a', 'b', 'c', 'd']);

        $this->assertSame(['a', 'b', 'c'], $result->data);
        $this->assertTrue($result->hasNext);
        $this->assertSame(2, $result->nextPage);
        $this->assertSame(3, $result->count);
    }

    #[Test]
    public function paginate_never_returns_more_than_the_configured_page_size(): void
    {
        $result = new SimplePaginator(itemsPerPage: 3)->paginate(['a', 'b', 'c', 'd', 'e']);

        $this->assertSame(['a', 'b', 'c'], $result->data);
        $this->assertTrue($result->hasNext);
        $this->assertSame(3, $result->count);
    }

    #[Test]
    public function it_handles_an_empty_first_page(): void
    {
        $result = new SimplePaginator()->paginate([]);

        $this->assertSame([], $result->data);
        $this->assertTrue($result->isEmpty);
        $this->assertFalse($result->isNotEmpty);
        $this->assertFalse($result->hasNext);
        $this->assertFalse($result->hasPrevious);
        $this->assertNull($result->nextPage);
        $this->assertNull($result->previousPage);
    }

    #[Test]
    public function a_page_beyond_the_available_data_still_has_a_previous_page(): void
    {
        $result = new SimplePaginator(itemsPerPage: 20, currentPage: 100)->paginate([]);

        $this->assertSame([], $result->data);
        $this->assertSame(1980, $result->offset);
        $this->assertFalse($result->hasNext);
        $this->assertTrue($result->hasPrevious);
        $this->assertNull($result->nextPage);
        $this->assertSame(99, $result->previousPage);
    }

    #[Test]
    public function paginate_with_requests_one_additional_item_at_the_correct_offset(): void
    {
        $requestedLimit = null;
        $requestedOffset = null;

        $result = new SimplePaginator(itemsPerPage: 2, currentPage: 2)->paginateWith(
            function (int $limit, int $offset) use (&$requestedLimit, &$requestedOffset): array {
                $requestedLimit = $limit;
                $requestedOffset = $offset;

                return ['c', 'd', 'e'];
            },
        );

        $this->assertSame(3, $requestedLimit);
        $this->assertSame(2, $requestedOffset);
        $this->assertSame(['c', 'd'], $result->data);
        $this->assertSame(2, $result->currentPage);
        $this->assertSame(2, $result->offset);
        $this->assertTrue($result->hasNext);
        $this->assertTrue($result->hasPrevious);
        $this->assertSame(3, $result->nextPage);
        $this->assertSame(1, $result->previousPage);
    }

    #[Test]
    #[DataProvider('invalidArgumentsProvider')]
    public function it_rejects_invalid_arguments(int $itemsPerPage, int $currentPage): void
    {
        $this->expectException(ArgumentWasInvalid::class);

        new SimplePaginator(itemsPerPage: $itemsPerPage, currentPage: $currentPage);
    }

    public static function invalidArgumentsProvider(): array
    {
        return [
            'zero items per page' => [0, 1],
            'negative items per page' => [-1, 1],
            'zero current page' => [20, 0],
            'negative current page' => [20, -1],
        ];
    }

    #[Test]
    public function it_maps_data_while_preserving_pagination_metadata(): void
    {
        $result = new SimplePaginator(itemsPerPage: 2, currentPage: 2)
            ->paginate([1, 2, 3])
            ->map(fn (int $value): string => "item-{$value}");

        $this->assertInstanceOf(SimplePaginatedData::class, $result);
        $this->assertSame(['item-1', 'item-2'], $result->data);
        $this->assertSame(2, $result->currentPage);
        $this->assertSame(2, $result->itemsPerPage);
        $this->assertSame(2, $result->offset);
        $this->assertSame(2, $result->limit);
        $this->assertTrue($result->hasNext);
        $this->assertTrue($result->hasPrevious);
        $this->assertSame(3, $result->nextPage);
        $this->assertSame(1, $result->previousPage);
    }

    #[Test]
    public function it_converts_to_an_array_and_json(): void
    {
        $result = new SimplePaginator(itemsPerPage: 2)->paginate(['a', 'b', 'c']);
        $expected = [
            'data' => ['a', 'b'],
            'pagination' => [
                'current_page' => 1,
                'items_per_page' => 2,
                'offset' => 0,
                'limit' => 2,
                'has_next' => true,
                'has_previous' => false,
                'next_page' => 2,
                'previous_page' => null,
                'count' => 2,
            ],
        ];

        $this->assertSame($expected, $result->toArray());
        $this->assertSame($expected, json_decode(json_encode($result, flags: JSON_THROW_ON_ERROR), associative: true, flags: JSON_THROW_ON_ERROR));
    }
}
