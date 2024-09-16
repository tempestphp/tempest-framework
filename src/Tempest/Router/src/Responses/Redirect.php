<?php

declare(strict_types=1);

namespace Tempest\Router\Responses;

use Tempest\Http\Status;
use Tempest\Router\IsResponse;
use Tempest\Router\Response;

final class Redirect implements Response
{
    use IsResponse;

    public function __construct(
        string $to,
    ) {
        $this->status = Status::FOUND;
        $this->addHeader('Location', $to);
    }

    public function permanent(): self
    {
        $this->status = Status::MOVED_PERMANENTLY;

        return $this;
    }
}
