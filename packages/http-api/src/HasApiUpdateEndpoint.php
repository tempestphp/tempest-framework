<?php

namespace Tempest\HttpApi;

use Tempest\Discovery\SkipDiscovery;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Status;
use Tempest\HttpApi\ApiResourceRoute;
use Tempest\HttpApi\ApiResponse;
use Tempest\HttpApi\HasResourceAttributes;
use Tempest\HttpApi\HasResourceRecord;

use function Tempest\Database\query;
use function Tempest\map;

#[SkipDiscovery]
trait HasApiUpdateEndpoint
{
    use HasResourceRecord;
    use HasResourceAttributes;

    #[ApiResourceRoute(self::class, method: Method::PATCH, uri: '{resourceRecordId}')]
    public function apiUpdate(string|int $resourceRecordId, Request $request): Response
    {
        $updatedRecordId = query(static::getResourceRecord())
            ->update($request->body)
            ->where('id = ?', $resourceRecordId)
            ->execute();

        return new ApiResponse(
            status: Status::OK,
            body: map(
                query(static::getResourceRecord())->select()->get($updatedRecordId),
            )->toArray(),
        );
    }
}
