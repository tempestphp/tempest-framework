<?php

namespace Tempest\Http;

use Tempest\Support\Json;

/**
 * Represents a message streamed through server-sent events.
 */
final class ServerSentMessage implements ServerSentEvent
{
    public function __construct(
        public mixed $data,
        public string $event = 'message',
    ) {}

    public array $datalines {
        get {
            return [
                "event: {$this->event}\n",
                "data: " . Json\encode($this->data),
                "\n\n",
            ];
        }
    }
}
