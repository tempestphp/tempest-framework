<?php

namespace Tempest\Http;

use Tempest\Interfaces\Container;
use Tempest\Interfaces\Server as ServerInterface;

final readonly class RequestInitializer
{
    public function __construct(private ServerInterface $server) {}

    public function __invoke(Container $container): GenericRequest
    {
    }
}