<?php

declare(strict_types=1);

namespace Tempest\Http\Responses;

use Tempest\Http\IsResponse;
use Tempest\Http\Response;
use Tempest\Http\Status;

final class Redirect implements Response
{
    use IsResponse;

    public function __construct(
        private(set) string $to,
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
