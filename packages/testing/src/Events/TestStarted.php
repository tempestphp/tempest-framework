<?php

namespace Tempest\Testing\Events;

use Tempest\EventBus\StopsPropagation;
use Tempest\Testing\Output\ConvertsToTeamcityMessage;
use Tempest\Testing\Output\TeamcityMessage;
use Tempest\Testing\Output\TeamcityMessageName;

#[StopsPropagation]
final class TestStarted implements DispatchToParentProcess, ConvertsToTeamcityMessage
{
    public function __construct(
        public string $name,
    ) {}

    public TeamcityMessage $teamcityMessage {
        get => new TeamcityMessage(
            TeamcityMessageName::TEST_STARTED,
            [
                'name' => $this->name,
            ],
        );
    }

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
