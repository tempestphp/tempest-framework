<?php

declare(strict_types=1);

namespace Tempest\Http\Responses;

use Closure;
use Generator;
use Tempest\DateTime\Duration;
use Tempest\Http\ContentType;
use Tempest\Http\IsResponse;
use Tempest\Http\Response;
use Tempest\Http\Status;

final class EventStream implements Response
{
    use IsResponse;

    public Duration $sleep;

    public function __construct(
        Closure $callback,
        int|Duration $sleep = 1000,
        Status $status = Status::OK,
    ) {
        $this->setContentType(ContentType::EVENT_STREAM);
        $this->addHeader('X-Accel-Buffering', 'no');
        $this->addHeader('Connection', 'keep-alive');
        $this->addHeader('Cache-Control', 'no-cache');

        $this->status = $status;
        $this->body = $this->createGeneratorFromCallback($callback);
        $this->sleep = is_int($sleep) ? Duration::milliseconds($sleep) : $sleep;
    }

    public function createGeneratorFromCallback($callback): Generator
    {
        yield from $callback();
    }
}
