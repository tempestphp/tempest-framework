<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core;

use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use function Tempest\path;
use function Tempest\root_path;

/**
 * @internal
 */
final class RootPathHelperTest extends FrameworkIntegrationTestCase
{
    public function test_can_get_base_path(): void
    {
        $this->assertSame(path(realpath($this->root))->toString(), root_path());
        $this->assertSame(path(realpath($this->root . '/tests/Fixtures'))->toString(), root_path('/tests/Fixtures'));
    }
}
