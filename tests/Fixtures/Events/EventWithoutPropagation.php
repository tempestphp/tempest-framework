<?php

namespace Tests\Tempest\Fixtures\Events;

use Tempest\EventBus\StopsPropagation;

#[StopsPropagation]
final readonly class EventWithoutPropagation
{
}
