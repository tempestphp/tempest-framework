<?php

namespace Tests\Tempest\Fixtures\Events;

use Tempest\EventBus\WithoutPropagation;

#[WithoutPropagation]
final readonly class EventWithoutPropagation
{
}
