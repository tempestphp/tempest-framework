<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Container\Singleton;
use Tempest\Core\Resetable;

#[Singleton]
final class RequestHolder implements Resetable
{
    private(set) Request $request;

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    public function reset(): void
    {
        unset($this->request);
    }
}
