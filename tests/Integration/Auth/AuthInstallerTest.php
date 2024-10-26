<?php

namespace Tests\Tempest\Integration\Auth;

use Tempest\Core\Composer;
use Tempest\Core\ComposerNamespace;
use Tempest\Core\Kernel;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class AuthInstallerTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $installDir = __DIR__ . '/install';
        
        mkdir($installDir);
        $this->container->get(Kernel::class)->root = $installDir;
        $this->container->get(Composer::class)->setMainNamespace(new ComposerNamespace('App\\', $installDir));
    }

    protected function tearDown(): void
    {
        rmdir(__DIR__ . '/install');
        
        parent::tearDown();
    }
    
    public function test_install(): void
    {
        
    }
}