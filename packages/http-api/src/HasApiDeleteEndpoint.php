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

#[SkipDiscovery]
trait HasApiDeleteEndpoint
{
    use HasResourceRecord;
    use HasResourceAttributes;

    #[ApiResourceRoute(self::class, method: Method::DELETE, uri: '{resourceRecordId}')]
    public function apiDelete(string|int $resourceRecordId): Response
    {
        // TODO: Validate if record exists?

        query(static::getResourceRecord())
            ->delete()
            ->where('id = ?', $resourceRecordId)
            ->execute();

        return new ApiResponse(
            status: Status::NO_CONTENT,
            body: [],
        );
    }
}
