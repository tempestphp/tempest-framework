<?php

namespace Tempest\Http;

/**
 * Represents a message streamed through server-sent events.
 */
final class ServerSentEvent
{
    public function __construct(
        public mixed $data,
        public string $event = 'message',
    ) {}
}
