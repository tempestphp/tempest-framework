<?php

namespace Tempest\HttpApi;

use Tempest\Discovery\SkipDiscovery;
use Tempest\Http\Method;
use Tempest\Http\Response;
use Tempest\Http\Status;
use Tempest\HttpApi\ApiResourceRoute;
use Tempest\HttpApi\ApiResponse;
use Tempest\HttpApi\HasResourceAttributes;
use Tempest\HttpApi\HasResourceRecord;

use function Tempest\Database\query;
use function Tempest\map;

#[SkipDiscovery]
trait HasApiShowEndpoint
{
    use HasResourceRecord;
    use HasResourceAttributes;

    #[ApiResourceRoute(self::class, method: Method::GET, uri: '{resourceRecordId}')]
    public function apiView(string|int $resourceRecordId): Response
    {
        $record = query(static::getResourceRecord())
            ->select()
            ->where('id = ?', $resourceRecordId)
            ->first();

        if ($record === null) {
            return new ApiResponse(
                status: Status::NOT_FOUND,
                body: [],
            );
        }

        return new ApiResponse(
            status: Status::OK,
            body: map($record)->toArray(),
        );
    }
}
