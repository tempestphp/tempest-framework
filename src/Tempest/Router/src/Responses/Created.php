<?php

declare(strict_types=1);

namespace Tempest\Router\Responses;

use Tempest\Http\Status;
use Tempest\Router\IsResponse;
use Tempest\Router\Response;
use Tempest\View\View;

final class Created implements Response
{
    use IsResponse;

    public function __construct(
        string|array|null|View $body = null,
    ) {
        $this->status = Status::CREATED;
        $this->body = $body;
    }
}
