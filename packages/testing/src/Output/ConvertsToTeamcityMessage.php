<?php

namespace Tempest\Testing\Output;

interface ConvertsToTeamcityMessage
{
    public TeamcityMessage $teamcityMessage {
        get;
    }
}
