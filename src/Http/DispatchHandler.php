<?php

namespace Tempest\Http;

final readonly class DispatchHandler
{
    public function __invoke(Dispatch $dispatch): void
    {
dd('hi');
    }
}