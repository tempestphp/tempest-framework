<?php

namespace Tempest\HttpApi;

use Tempest\Discovery\SkipDiscovery;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Http\Status;
use Tempest\HttpApi\ApiResourceRoute;
use Tempest\HttpApi\ApiResponse;
use Tempest\HttpApi\HasResourceAttributes;
use Tempest\HttpApi\HasResourceRecord;

use function Tempest\Database\query;
use function Tempest\map;

#[SkipDiscovery]
trait HasApiCreateEndpoint
{
    use HasResourceRecord;
    use HasResourceAttributes;

    #[ApiResourceRoute(self::class, method: Method::POST)]
    public function apiCreate(Request $request): ApiResponse
    {
        $recordId = query(static::getResourceRecord())
            ->insert($request->body)
            ->execute();

        $record = query(static::getResourceRecord())
            ->select()
            ->get($recordId);

        return new ApiResponse(
            status: Status::CREATED,
            body: map($record)->toArray(),
        );
    }
}
