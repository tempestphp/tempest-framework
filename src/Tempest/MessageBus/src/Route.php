<?php

namespace Tempest\MessageBus;

final class Route
{
    private(set) string $messageName;

    /**
     * @var array<string>
     */
    private(set) array $exchange = [];

    /**
     * @var array<string>
     */
    private(set) array $queues = [];

    /**
     * @var array<\Closure> $filters
     */
    private(set) array $filters = [];

    public function __construct(string $messageName)
    {
        $this->messageName = $messageName;
    }

    public function toExchange(string $exchange): self
    {
        $this->exchange[] = $exchange;
    }

    public function toQueue(string $queue): self
    {
        $this->queues[] = $queue;
    }

    public function when(callable $filter): self
    {
        $this->filters[] = $filter;

        return $this;
    }
}

new Route('pets.*')
    ->toQueue('pets')
    ->when(
        fn (Envelope $message) => $message->hasHeader(''),
    );