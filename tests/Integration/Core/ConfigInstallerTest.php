<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Core\Kernel\LoadConfig;
use Tempest\Support\Namespace\Psr4Namespace;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Support\arr;
use function Tempest\Support\path;
use function Tempest\Support\str;

/**
 * @internal
 */
final class ConfigInstallerTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->installer
            ->configure(
                $this->internalStorage . '/install',
                new Psr4Namespace('App\\', $this->internalStorage . '/install/App'),
            )
            ->setRoot($this->internalStorage . '/install');
    }

    protected function tearDown(): void
    {
        $this->installer->clean();

        parent::tearDown();
    }

    #[Test]
    public function it_can_install(): void
    {
        $loadConfig = $this->container->get(LoadConfig::class);

        $firstOption = arr($loadConfig->find())
            ->filter(fn (string $path) => str($path)->contains(['/packages/', '/vendor/']))
            ->first();

        $this->console
            ->call('install config --force')
            ->submit(0)
            ->assertSuccess();

        $this->installer
            ->assertFileExists(
                path: path('App/Config', pathinfo($firstOption, PATHINFO_BASENAME))->toString(),
                content: file_get_contents($firstOption),
            );
    }
}
