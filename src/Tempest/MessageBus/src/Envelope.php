<?php

namespace Tempest\MessageBus;

final class Envelope
{
    public function __construct(
        private(set) string $messageName,
        private(set) array $payload = [],
        private(set) array $headers = [],
    ) {}
}