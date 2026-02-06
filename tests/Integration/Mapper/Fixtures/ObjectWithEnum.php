<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Http\Method;
use Tempest\Mapper\SerializeAs;

#[SerializeAs(self::class)]
final class ObjectWithEnum
{
    public Method $method;
}
