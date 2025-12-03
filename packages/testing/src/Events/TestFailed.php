<?php

namespace Tempest\Testing\Events;

use Tempest\EventBus\StopsPropagation;
use Tempest\Testing\Exceptions\TestHasFailed;
use Tempest\Testing\Output\ConvertsToTeamcityMessage;
use Tempest\Testing\Output\TeamcityMessage;
use Tempest\Testing\Output\TeamcityMessageName;

#[StopsPropagation]
final class TestFailed implements DispatchToParentProcess, ConvertsToTeamcityMessage
{
    public function __construct(
        public string $name,
        public string $reason,
        public string $location,
    ) {}

    public TeamcityMessage $teamcityMessage {
        get => new TeamcityMessage(
            TeamcityMessageName::TEST_FAILED,
            [
                'name' => $this->name,
                'message' => $this->reason,
                'details' => $this->location,
            ],
        );
    }

    public static function fromException(string $name, TestHasFailed $exception): self
    {
        return new self(
            name: $name,
            reason: $exception->reason,
            location: $exception->location,
        );
    }

    public function serialize(): array
    {
        return [
            'name' => $this->name,
            'reason' => $this->reason,
            'location' => $this->location,
        ];
    }

    public static function deserialize(array $data): DispatchToParentProcess
    {
        return new self(
            name: $data['name'],
            reason: $data['reason'],
            location: $data['location'],
        );
    }
}
