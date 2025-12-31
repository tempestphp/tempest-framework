<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration;

use InvalidArgumentException;
use Stringable;
use Tempest\Database\DatabaseInitializer;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Framework\Testing\IntegrationTest;
use Tempest\Reflection\MethodReflector;
use Tempest\Router\Route;
use Tempest\Router\RouteConfig;
use Tempest\Router\RouteDecorator;
use Tempest\Router\Routing\Construction\DiscoveredRoute;
use Tempest\Router\Routing\Construction\RouteConfigurator;
use Tempest\Router\Static\StaticPageConfig;
use Tempest\Router\StaticPage;
use Tempest\Support\Filesystem;
use Tempest\Support\Path;
use Tempest\View\GenericView;
use Tempest\View\View;
use Tempest\View\ViewComponent;
use Tempest\View\ViewConfig;
use Tempest\View\ViewRenderer;

use function Tempest\Support\str;

abstract class FrameworkIntegrationTestCase extends IntegrationTest
{
    protected function discoverTestLocations(): array
    {
        return [
            new DiscoveryLocation('Tests\\Tempest\\Integration\\Console\\Fixtures', __DIR__ . '/Console/Fixtures'),
            new DiscoveryLocation('Tests\\Tempest\\Fixtures', __DIR__ . '/../Fixtures'),
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->container
            ->removeInitializer(DatabaseInitializer::class)
            ->addInitializer(TestingDatabaseInitializer::class);

        $defaultDatabaseConfigPath = Path\normalize(__DIR__, '..', 'Fixtures/Config/database.sqlite.php');
        $databaseConfigPath = Path\normalize(__DIR__, '..', 'Fixtures/Config/database.config.php');

        if (! Filesystem\exists($databaseConfigPath)) {
            Filesystem\copy_file($defaultDatabaseConfigPath, $databaseConfigPath);
        }

        $this->container->config(require $databaseConfigPath);
        $this->database->reset(migrate: false);
    }

    protected function render(string|View $view, mixed ...$params): string
    {
        if (is_string($view)) {
            $view = new GenericView($view);
        }

        $view->data(...$params);

        return $this->container->get(ViewRenderer::class)->render($view);
    }

    protected function registerViewComponent(string $name, string $html, string $file = '', bool $isVendor = false): void
    {
        $viewComponent = new ViewComponent(
            name: $name,
            contents: $html,
            file: $file,
            isVendorComponent: $isVendor,
        );

        $this->container->get(ViewConfig::class)->addViewComponent($viewComponent);
    }

    protected function assertStringCount(string $subject, string $search, int $count): void
    {
        $this->assertSame($count, substr_count($subject, $search));
    }

    protected function registerRoute(array|string|MethodReflector $action): void
    {
        $reflector = match (true) {
            $action instanceof MethodReflector => $action,
            is_array($action) => MethodReflector::fromParts(...$action),
            default => MethodReflector::fromParts($action, '__invoke'),
        };

        if ($reflector->getAttribute(Route::class) === null) {
            throw new InvalidArgumentException('Missing route attribute');
        }

        $configurator = $this->container->get(RouteConfigurator::class);

        $configurator->addRoute(
            DiscoveredRoute::fromRoute(
                $reflector->getAttribute(Route::class),
                [
                    ...$reflector->getDeclaringClass()->getAttributes(RouteDecorator::class),
                    ...$reflector->getAttributes(RouteDecorator::class),
                ],
                $reflector,
            ),
        );

        $routeConfig = $this->container->get(RouteConfig::class);
        $routeConfig->apply($configurator->toRouteConfig());
    }

    protected function registerStaticPage(array|string|MethodReflector $action): void
    {
        $reflector = match (true) {
            $action instanceof MethodReflector => $action,
            is_array($action) => MethodReflector::fromParts(...$action),
            default => MethodReflector::fromParts($action, '__invoke'),
        };

        if ($reflector->getAttribute(StaticPage::class) === null) {
            throw new InvalidArgumentException('Missing static page attribute');
        }

        $this->container->get(StaticPageConfig::class)->addHandler(
            $reflector->getAttribute(StaticPage::class),
            $reflector,
        );
    }

    protected function assertSnippetsMatch(string $expected, string $actual): void
    {
        $expected = str_replace([PHP_EOL, ' '], '', $expected);
        $actual = str_replace([PHP_EOL, ' '], '', $actual);

        $this->assertSame($expected, $actual);
    }

    protected function assertSameWithoutBackticks(Stringable|string $expected, Stringable|string $actual): void
    {
        $clean = function (string $string): string {
            return str($string)
                ->replace('`', '')
                ->replaceRegex('/AS \"(?<alias>.*?)\"/', fn (array $matches) => "AS {$matches['alias']}")
                ->toString();
        };

        $this->assertSame(
            $clean((string) $expected),
            $clean((string) $actual),
        );
    }

    protected function skipWindows(string $reason): void
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return;
        }

        $this->markTestSkipped($reason);
    }

    protected function skipCI(string $reason): void
    {
        if (getenv('CI') !== 'true') {
            return;
        }

        $this->markTestSkipped($reason);
    }

    /**
     * @template TClassName of object
     * @param class-string<TClassName> $className
     * @return null|TClassName
     */
    protected function get(string $className): ?object
    {
        return $this->container->get($className);
    }
}
