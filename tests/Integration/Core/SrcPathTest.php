<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core;

use Tempest\Core\Composer;
use Tempest\Core\ComposerNamespace;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\root_path;
use function Tempest\src_path;

/**
 * @internal
 */
final class SrcPathTest extends FrameworkIntegrationTestCase
{
    public function test_can_get_src_path(): void
    {
        $this->container->get(Composer::class)->setMainNamespace(new ComposerNamespace('App\\', root_path('/app')));

        $this->assertSame(root_path('/app'), src_path());
        $this->assertSame(root_path('/app/User.php'), src_path('User.php'));
    }
}
