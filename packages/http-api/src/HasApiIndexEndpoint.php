<?php

namespace Tempest\HttpApi;

use Tempest\Database\Builder\QueryBuilders\SelectQueryBuilder;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Http\Method;
use Tempest\Http\Response;
use Tempest\Http\Status;
use Tempest\HttpApi\ApiResourceRoute;
use Tempest\HttpApi\ApiResponse;
use Tempest\HttpApi\CursorPagination;
use Tempest\HttpApi\HasResourceAttributes;
use Tempest\HttpApi\HasResourceRecord;
use Tempest\HttpApi\OffsetPagination;

use function Tempest\Database\query;
use function Tempest\map;

#[SkipDiscovery]
trait HasApiIndexEndpoint
{
    use HasResourceRecord;
    use HasResourceAttributes;

    #[ApiResourceRoute(self::class, method: Method::GET)]
    public function apiIndex(IndexApiRequest $request): Response
    {
        $limit = $request->perPage ?? static::getResourcePagination()->limit ?? 15;

        $countQuery = query(static::getResourceRecord())
            ->count();

        $records = query(static::getResourceRecord())
            ->select()
            ->when(
                $request->search !== null && trim($request->search) !== '',
                function (SelectQueryBuilder $query) use ($request, &$countQuery): SelectQueryBuilder {
                    $searchableColumns = static::getResourceSearchableColumns();

                    if ($searchableColumns === null) {
                        return $query;
                    }

                    $query = $query->where(array_shift($searchableColumns) . ' LIKE ?', "%{$request->search}%");
                    $countQuery = $countQuery->where(array_shift($searchableColumns) . ' LIKE ?', "%{$request->search}%");

                    foreach ($searchableColumns as $column) {
                        $query = $query->orWhere($column . ' LIKE ?', "%{$request->search}%");
                        $countQuery = $countQuery->orWhere($column . ' LIKE ?', "%{$request->search}%");
                    }

                    return $query;
                },
            )
            ->when(
                $request->sort !== null,
                function (SelectQueryBuilder $query) use ($request): SelectQueryBuilder {
                    $direction = $request->direction ?? 'asc';

                    return $query->orderBy("{$request->sort} {$direction}");
                },
            )
            ->when(
                static::getResourcePagination() instanceof OffsetPagination,
                function (SelectQueryBuilder $query) use ($request, $limit): SelectQueryBuilder {
                    $offset = (($request->page ?? 1) - 1) * $limit;

                    return $query->limit($limit)->offset($offset);
                },
            )
            ->when(
                static::getResourcePagination() instanceof CursorPagination,
                function (SelectQueryBuilder $query) use ($request, $limit): SelectQueryBuilder {
                    if ($request->cursor) {
                        $query = $query->where(static::getResourcePagination()->key, '>', $request->cursor);
                    }

                    return $query->limit($limit);
                },
            )
            ->all();

        $totalCount = $countQuery->execute();
        $headers = [];
        $extraData = [];

        if (static::getResourcePagination() instanceof OffsetPagination) {
            $totalPages = (int) ceil($totalCount / $limit);

            $headers = [
                ...$headers,
                'X-Total-Count' => (string) $totalCount,
                'X-Total-Pages' => (string) $totalPages,
                'X-Current-Page' => (string) ($request->page ?? 1),
                'X-Per-Page' => (string) $limit,
            ];

            $extraData = [
                'pagination' => [
                    'total_count' => $totalCount,
                    'total_pages' => $totalPages,
                    'current_page' => $request->page ?? 1,
                    'per_page' => $limit,
                ],
            ];
        } else if (static::getResourcePagination() instanceof CursorPagination) {
            $nextCursor = null;
            $prevCursor = null;

            if (count($records) <= 0) {
                $nextCursor = end($records)->id ?? null;
                $prevCursor = reset($records)->id ?? null;
            }

            $headers = [
                ...$headers,
                'X-Next-Cursor' => (string) $nextCursor,
                'X-Prev-Cursor' => (string) $prevCursor,
                'X-Per-Page' => (string) $limit,
            ];

            $extraData = [
                'pagination' => [
                    'next_cursor' => $nextCursor,
                    'prev_cursor' => $prevCursor,
                    'per_page' => $limit,
                ],
            ];
        }

        return new ApiResponse(
            status: Status::OK,
            body: array_map(
                fn ($record) => map($record)->toArray(),
                $records,
            ),
            headers: $headers,
            extraData: $extraData,
        );
    }
}
