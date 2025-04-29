<?php

declare(strict_types=1);

namespace Tempest\Http\Stubs;

use Tempest\Http\IsResponse;
use Tempest\Http\Response;
use Tempest\Http\Status;

final class ResponseStub implements Response
{
    use IsResponse;

    public function __construct()
    {
        $this->setStatus(Status::OK);
        $this->addHeader('Content-Type', 'application/json');
    }
}
