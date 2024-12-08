<?php

declare(strict_types=1);

namespace Tempest\Router\Stubs;

use Tempest\Http\Status;
use Tempest\Router\IsResponse;
use Tempest\Router\Response;

final class ResponseStub implements Response
{
    use IsResponse;

    public function __construct()
    {
        $this->setStatus(Status::OK);
        $this->addHeader('Content-Type', 'application/json');
    }
}
