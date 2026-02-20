<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Container\Singleton;

#[Singleton]
final class RequestHolder
{
    private(set) Request $request;

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    public function clear(): void
    {
        unset($this->request);
    }
}
