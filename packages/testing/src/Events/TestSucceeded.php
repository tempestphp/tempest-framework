<?php

namespace Tempest\Testing\Events;

use Tempest\EventBus\StopsPropagation;

#[StopsPropagation]
final class TestSucceeded implements DispatchToParentProcess
{
    public function __construct(
        public string $name,
    ) {}

    public function serialize(): array
    {
        return [
            'name' => $this->name,
        ];
    }

    public static function deserialize(array $data): DispatchToParentProcess
    {
        return new self(
            name: $data['name'],
        );
    }
}
