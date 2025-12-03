<?php

namespace Tempest\Testing\Events;

use Tempest\EventBus\StopsPropagation;
use Tempest\Testing\Output\ConvertsToTeamcityMessage;
use Tempest\Testing\Output\TeamcityMessage;
use Tempest\Testing\Output\TeamcityMessageName;

#[StopsPropagation]
final class TestSkipped implements ConvertsToTeamcityMessage
{
    public function __construct(
        public string $name,
    ) {}

    public TeamcityMessage $teamcityMessage {
        get => new TeamcityMessage(
            TeamcityMessageName::TEST_IGNORED,
            [
                'name' => $this->name,
            ],
        );
    }
}
