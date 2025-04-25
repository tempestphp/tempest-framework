<?php

namespace Tempest\MessageBus;

final class Topic
{
    private(set) array $toQueues = [];

    public function __construct(
        private(set) string $name
    ) {}

    public function toQueue(string $queue): self
    {
        $this->toQueues[] = $queue;

        return $this;
    }
}