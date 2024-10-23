<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core;

use function Tempest\path;
use function Tempest\root_path;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class RootPathHelperTest extends FrameworkIntegrationTestCase
{
    public function test_can_get_base_path(): void
    {
        $this->assertSame(path(realpath($this->root)), root_path());
        $this->assertSame(path(realpath($this->root . '/tests/Fixtures')), root_path('/tests/Fixtures'));
    }
}
