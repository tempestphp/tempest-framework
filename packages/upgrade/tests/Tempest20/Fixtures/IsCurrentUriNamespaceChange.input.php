<?php

namespace Tempest\Upgrade\Tests\Tempest20\Fixtures;

use function Tempest\is_current_uri;

final class IsCurrentUriNamespaceChange
{
    public function __invoke()
    {
        return is_current_uri(self::class);
    }
}
