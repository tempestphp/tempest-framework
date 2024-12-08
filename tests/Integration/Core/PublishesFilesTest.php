<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Core\Composer;
use Tempest\Core\ComposerNamespace;
use Tests\Tempest\Fixtures\Core\PublishesFilesConcreteClass;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use function Tempest\path;

/**
 * @internal
 */
final class PublishesFilesTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->installer->configure(
            __DIR__ . '/install',
            new ComposerNamespace('App\\', __DIR__ . '/install/App'),
        );
    }

    protected function tearDown(): void
    {
        $this->installer->clean();

        parent::tearDown();
    }

    #[DataProvider('suggested_path_provider')]
    #[Test]
    public function get_suggested_path(
        string $className,
        ?string $pathPrefix,
        ?string $classSuffix,
        string $expected,
    ): void {
        $composer = $this->container->get(Composer::class);
        $concreteClass = $this->container->get(PublishesFilesConcreteClass::class);
        $appPath = str_replace('\\', '/', $composer->mainNamespace->path); // Normalize windows path

        $this->assertSame(
            path($appPath, $expected)->toString(),
            $concreteClass->getSuggestedPath(
                className: $className,
                pathPrefix: $pathPrefix,
                classSuffix: $classSuffix,
            ),
        );
    }

    public static function suggested_path_provider(): array
    {
        return [
            'Basic' => [
                'className' => 'Hello',
                'pathPrefix' => null,
                'classSuffix' => null,
                'expected' => 'Hello.php',
            ],
            'Path prefix' => [
                'className' => 'Hello',
                'pathPrefix' => 'World',
                'classSuffix' => null,
                'expected' => 'World/Hello.php',
            ],
            'Class suffix' => [
                'className' => 'Hello',
                'pathPrefix' => null,
                'classSuffix' => 'World',
                'expected' => 'HelloWorld.php',
            ],
            'Path prefix and class suffix' => [
                'className' => 'Hello',
                'pathPrefix' => 'World',
                'classSuffix' => 'Universe',
                'expected' => 'World/HelloUniverse.php',
            ],
            'Class suffix duplicated in input' => [
                'className' => 'HelloWorld',
                'pathPrefix' => null,
                'classSuffix' => 'World',
                'expected' => 'HelloWorld.php',
            ],
            'Class suffix duplicated in input and path prefix' => [
                'className' => 'HelloWorld',
                'pathPrefix' => 'World',
                'classSuffix' => 'World',
                'expected' => 'World/HelloWorld.php',
            ],
            'ClassName with multiple parts' => [
                'className' => 'Hello/World',
                'pathPrefix' => null,
                'classSuffix' => null,
                'expected' => 'Hello/World.php',
            ],
            'ClassName with multiple parts and path prefix' => [
                'className' => 'Hello/World',
                'pathPrefix' => 'Universe',
                'classSuffix' => null,
                'expected' => 'Universe/Hello/World.php',
            ],
            'ClassName with multiple parts and class suffix' => [
                'className' => 'Hello/World',
                'pathPrefix' => null,
                'classSuffix' => 'Universe',
                'expected' => 'Hello/WorldUniverse.php',
            ],
            'ClassName with multiple parts, path prefix and class suffix' => [
                'className' => 'Hello/World',
                'pathPrefix' => 'Universe',
                'classSuffix' => 'Galaxy',
                'expected' => 'Universe/Hello/WorldGalaxy.php',
            ],
            'ClassName with multiple parts namespaced' => [
                'className' => 'Hello\\World',
                'pathPrefix' => null,
                'classSuffix' => null,
                'expected' => 'Hello/World.php',
            ],
            'ClassName with multiple parts namespaced and path prefix' => [
                'className' => 'Hello\\World',
                'pathPrefix' => 'Universe',
                'classSuffix' => null,
                'expected' => 'Universe/Hello/World.php',
            ],
            'ClassName with multiple parts namespaced and class suffix' => [
                'className' => 'Hello\\World',
                'pathPrefix' => null,
                'classSuffix' => 'Universe',
                'expected' => 'Hello/WorldUniverse.php',
            ],
            'ClassName with multiple parts namespaced, path prefix and class suffix' => [
                'className' => 'Hello\\World',
                'pathPrefix' => 'Universe',
                'classSuffix' => 'Galaxy',
                'expected' => 'Universe/Hello/WorldGalaxy.php',
            ],
            'ClassName with multiple parts and classname contained in path' => [
                'className' => 'Books/Book',
                'pathPrefix' => null,
                'classSuffix' => null,
                'expected' => 'Books/Book.php',
            ],
        ];
    }
}
