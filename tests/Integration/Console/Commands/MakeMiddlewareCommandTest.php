<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Commands;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Console\Enums\MiddlewareType;
use Tempest\Core\ComposerNamespace;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class MakeMiddlewareCommandTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->installer->configure(
            __DIR__ . '/install',
            new ComposerNamespace('App\\', __DIR__ . '/install/App')
        );
    }

    protected function tearDown(): void
    {
        $this->installer->clean();

        parent::tearDown();
    }

    #[Test]
    #[DataProvider('command_input_provider')]
    public function make_command(
        string $commandArgs,
        string $expectedPath,
        string $expectedNamespace
    ): void {
        $this->console
            ->call("make:middleware {$commandArgs}")
            ->submit();

        $this->installer
            ->assertFileExists($expectedPath)
            ->assertFileContains($expectedPath, 'namespace ' . $expectedNamespace . ';');
    }

    public static function command_input_provider(): array
    {
        return [
            'make_with_defaults' => [
                'commandArgs' => 'BookMiddleware http',
                'expectedPath' => 'App/BookMiddleware.php',
                'expectedNamespace' => 'App',
            ],
            'make_with_other_namespace' => [
                'commandArgs' => 'Middlewares\\BookMiddleware http',
                'expectedPath' => 'App/Middlewares/BookMiddleware.php',
                'expectedNamespace' => 'App\\Middlewares',
            ],
            'make_with_input_path' => [
                'commandArgs' => 'Middlewares/BookMiddleware http',
                'expectedPath' => 'App/Middlewares/BookMiddleware.php',
                'expectedNamespace' => 'App\\Middlewares',
            ],
        ];
    }

    #[Test]
    #[DataProvider('middleware_type_provider')]
    public function make_command_with_each_type(
        MiddlewareType $middlewareType,
        string $middlewareInterface
    ): void {
        $this->console
            ->call("make:middelware TestMiddleware {$middlewareType->value}")
            ->submit();

        $filepath = 'App/TestMiddleware.php';
        $this->installer
            ->assertFileExists($filepath)
            ->assertFileContains($filepath, 'implements ' . $middlewareInterface);
    }

    public static function middleware_type_provider(): array
    {
        $cases = MiddlewareType::cases();

        return array_combine(
            keys: array_map(fn (MiddlewareType $case) => $case->value, $cases),
            values: array_map(fn (MiddlewareType $case) => [
                'middlewareType' => $case,
                'middlewareInterface' => $case->relatedInterface(),
            ], $cases)
        );
    }
}
