<?php

namespace Tempest\HttpApi;

use Tempest\Discovery\SkipDiscovery;
use Tempest\HttpApi\HasApiCreateEndpoint;
use Tempest\HttpApi\HasApiDeleteEndpoint;
use Tempest\HttpApi\HasApiIndexEndpoint;
use Tempest\HttpApi\HasApiShowEndpoint;
use Tempest\HttpApi\HasApiUpdateEndpoint;

#[SkipDiscovery]
trait IsApiResource
{
    use HasApiShowEndpoint;
    use HasApiIndexEndpoint;
    use HasApiDeleteEndpoint;
    use HasApiCreateEndpoint;
    use HasApiUpdateEndpoint;
}
