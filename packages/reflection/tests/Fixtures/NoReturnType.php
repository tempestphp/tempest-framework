<?php

declare(strict_types=1);

namespace Tempest\Reflection\Tests\Fixtures;

final class NoReturnType
{
    // @mago-expect lint:return-type
    public function noReturnType()
    {
        return 2137;
    }
}
