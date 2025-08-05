<?php

namespace Tempest\Http;

use JsonSerializable;
use Stringable;
use Tempest\DateTime\Duration;

interface ServerSentEvent
{
    /**
     * Defines the ID of this event, which sets the `Last-Event-ID` header in case of a reconnection.
     */
    public ?int $id {
        get;
    }

    /**
     * Defines the event stream's reconnection time in case of a reconnection attempt.
     */
    public null|Duration|int $retryAfter {
        get;
    }

    /**
     * The name of the event, which may be listened to by `EventSource#addEventListener`.
     *
     * **Example**
     * ```js
     * const eventSource = new EventSource('/events');
     * eventSource.addEventListener('my-event', (event) => {
     *   console.log(event.data)
     * })
     * ```
     */
    public ?string $event {
        get;
    }

    /**
     * Content of the event.
     */
    public JsonSerializable|Stringable|string|iterable $data {
        get;
    }
}
