<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Generation;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Snapshots\MatchesSnapshots;
use Tempest\Auth\Installer\oauth\OAuthControllerStub;
use Tempest\CommandBus\Installer\CreateCommandsTable;
use Tempest\Console\Stubs\CommandBusMiddlewareStub;
use Tempest\Console\Stubs\CommandStub;
use Tempest\Console\Stubs\ConsoleMiddlewareStub;
use Tempest\Console\Stubs\EventBusMiddlewareStub;
use Tempest\Console\Stubs\GeneratorCommandStub;
use Tempest\Console\Stubs\HttpMiddlewareStub;
use Tempest\Container\Singleton;
use Tempest\Container\Stubs\InitializerStub;
use Tempest\Database\Stubs\DatabaseModelStub;
use Tempest\Database\Stubs\ObjectAlterMigrationStub;
use Tempest\Database\Stubs\ObjectMigrationStub;
use Tempest\Database\Stubs\UpAlterMigrationStub;
use Tempest\Database\Stubs\UpMigrationStub;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Discovery\Stubs\DiscoveryStub;
use Tempest\Generation\Php\ClassManipulator;
use Tempest\Generation\Php\DataObjects\StubFile;
use Tempest\Generation\Php\StubFileGenerator;
use Tempest\Http\Session\CleanupSessionsCommand;
use Tempest\Http\Session\Installer\CreateSessionsTable;
use Tempest\Http\Stubs\RequestStub;
use Tempest\Http\Stubs\ResponseStub;
use Tempest\Router\Stubs\ControllerStub;
use Tempest\Support\Namespace\Psr4Namespace;
use Tempest\View\Stubs\ViewStub;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class StubFileGeneratorTest extends FrameworkIntegrationTestCase
{
    use MatchesSnapshots;

    protected function setUp(): void
    {
        parent::setUp();

        $this->installer->configure(
            $this->internalStorage . '/install',
            new Psr4Namespace('App\\', $this->internalStorage . '/install/App'),
        );
    }

    protected function tearDown(): void
    {
        $this->installer->clean();

        parent::tearDown();
    }

    #[Test]
    #[DataProvider('production_stubs')]
    public function generates_class_files_from_production_stubs(string $stub, array $replacements = [], array $manipulations = []): void
    {
        $targetPath = $this->installer->path('App/Generated/GeneratedClass.php');

        $this->container->get(StubFileGenerator::class)->generateClassFile(
            stubFile: StubFile::from($stub),
            targetPath: $targetPath,
            replacements: $replacements,
            manipulations: [
                static fn (ClassManipulator $class) => $class->removeClassAttribute(SkipDiscovery::class),
                ...$manipulations,
            ],
        );

        $code = $this->installer->get('App/Generated/GeneratedClass.php');

        $this->assertNotEmpty(token_get_all($code, TOKEN_PARSE));

        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Snapshots are not supported on Windows.');
        }

        $this->assertMatchesSnapshot($code);
    }

    public static function production_stubs(): iterable
    {
        yield 'command' => [CommandStub::class, ['dummy-command-slug' => 'generated-class']];
        yield 'generator command' => [GeneratorCommandStub::class, ['dummy-command-slug' => 'generated-class']];
        yield 'console middleware' => [ConsoleMiddlewareStub::class];
        yield 'http middleware' => [HttpMiddlewareStub::class];
        yield 'event bus middleware' => [EventBusMiddlewareStub::class];
        yield 'command bus middleware' => [CommandBusMiddlewareStub::class];
        yield 'initializer' => [InitializerStub::class];
        yield 'singleton initializer' => [
            InitializerStub::class,
            [],
            [static fn (ClassManipulator $class) => $class->addMethodAttribute('initialize', Singleton::class)],
        ];
        yield 'discovery' => [DiscoveryStub::class];
        yield 'controller' => [ControllerStub::class, ['dummy-path' => 'books', 'dummy-view' => 'books.view.php']];
        yield 'database model' => [DatabaseModelStub::class];
        yield 'object migration' => [ObjectMigrationStub::class, ['dummy-date' => '2026-10-27', 'dummy-migration-name' => 'create_books_table', 'dummy-table-name' => 'books']];
        yield 'object alter migration' => [
            ObjectAlterMigrationStub::class,
            ['dummy-date' => '2026-10-27', 'dummy-migration-name' => 'alter_books_table', 'dummy-table-name' => 'books'],
        ];
        yield 'up migration' => [UpMigrationStub::class, ['dummy-date' => '2026-10-27', 'dummy-migration-name' => 'create_books_table', 'dummy-table-name' => 'books']];
        yield 'up alter migration' => [UpAlterMigrationStub::class, ['dummy-date' => '2026-10-27', 'dummy-migration-name' => 'alter_books_table', 'dummy-table-name' => 'books']];
        yield 'request' => [RequestStub::class];
        yield 'response' => [ResponseStub::class];
        yield 'view' => [ViewStub::class, ['dummy.view.php' => 'generated-class.view.php']];
        yield 'users table migration' => [__DIR__ . '/../../../packages/auth/src/Installer/basic-user/CreateUsersTableMigration.stub.php'];
        yield 'user model' => [__DIR__ . '/../../../packages/auth/src/Installer/basic-user/UserModel.stub.php'];
        yield 'authentication decorator' => [__DIR__ . '/../../../packages/auth/src/Installer/basic-user/MustBeAuthenticated.stub.php'];
        yield 'authentication middleware' => [__DIR__ . '/../../../packages/auth/src/Installer/basic-user/MustBeAuthenticatedMiddleware.stub.php'];
        yield 'oauth controller' => [OAuthControllerStub::class];
        yield 'commands table migration' => [CreateCommandsTable::class];
        yield 'sessions table migration' => [CreateSessionsTable::class];
        yield 'cleanup sessions command' => [CleanupSessionsCommand::class];
    }
}
