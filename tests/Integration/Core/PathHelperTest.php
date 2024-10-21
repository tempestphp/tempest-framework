<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core;

use function Tempest\base_path;
use function Tempest\path;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class PathHelperTest extends FrameworkIntegrationTestCase
{
    public function test_can_get_base_path(): void
    {
        $this->assertSame(path(realpath($this->root)), base_path());
        $this->assertSame(path(realpath($this->root . '/tests/Fixtures')), base_path('/tests/Fixtures'));
    }
}
