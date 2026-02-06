<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration;

use Stringable;
use Tempest\Database\Builder\ModelInspector;
use Tempest\Database\DatabaseInitializer;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Framework\Testing\IntegrationTest;
use Tempest\Support\Filesystem;
use Tempest\Support\Path;

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

        ModelInspector::reset();
    }

    protected function assertStringCount(string $subject, string $search, int $count): void
    {
        $this->assertSame($count, substr_count($subject, $search));
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
