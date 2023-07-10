<?php

namespace Tempest\Route;

use Tempest\Container\InitializedBy;
use Tempest\Interfaces\Request;

#[InitializedBy(RequestInitializer::class)]
final class GenericRequest implements Request
{
    use BaseRequest;
}