<?php

namespace Tempest\Http;

use Tempest\Container\InitializedBy;
use Tempest\Interfaces\Request;

final class GenericRequest implements Request
{
    use BaseRequest;
}