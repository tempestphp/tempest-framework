<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core;

use Tempest\Core\Composer;
use Tempest\Core\ComposerNamespace;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\root_path;
use function Tempest\src_namespace;

/**
 * @internal
 */
final class SrcNamespaceTest extends FrameworkIntegrationTestCase
{
    public function test_can_get_src_namespace(): void
    {
        $this->container->get(Composer::class)->setMainNamespace(new ComposerNamespace('App\\', root_path('/app')));

        $this->assertSame('App', src_namespace());
        $this->assertSame('App\Auth\Foo', src_namespace('Auth\Foo'));
    }
}
