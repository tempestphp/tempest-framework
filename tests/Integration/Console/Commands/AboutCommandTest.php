<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Commands;

use Tempest\Console\Commands\SystemInfoProvider;
use Tempest\Database\Config\MysqlConfig;
use Tempest\Database\Config\PostgresConfig;
use Tempest\Database\Config\SQLiteConfig;
use Tempest\Database\Database;
use Tempest\Support\Namespace\Psr4Namespace;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class AboutCommandTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        putenv('CACHE=true');
        putenv('DISCOVERY_CACHE=false');

        $this->installer->configure(
            __DIR__ . '/install',
            new Psr4Namespace('App\\', __DIR__ . '/install/App'),
        );
    }

    protected function tearDown(): void
    {
        $this->installer->clean();

        // Reinit variables
        putenv('CACHE');
        putenv('DISCOVERY_CACHE');

        parent::tearDown();
    }

    /**
     * Tests the about command behavior when environment variables are missing.
     */
    public function test_missing_environment_variables(): void
    {
        // Unset the URL environment variable
        putenv('BASE_URI');

        // Execute the about command
        $this->console
            ->call('about')
            ->assertSuccess()
            ->assertSee('URL')
            ->assertSee('Not set'); // Adjust based on actual output when URL is missing
    }

    public function test_standard_output(): void
    {
        $this->console
            ->call('about')
            ->assertSuccess()
            ->assertContains('ENVIRONMENT')
            ->assertContains('DATABASE')
            ->assertContains('CACHE')
            ->assertContains('TAILWINDCSS')
            ->assertContains('URL');
    }

    public function test_standard_output_with_wrong_arg(): void
    {
        $this->console
            ->call('about -stuff')
            ->assertSuccess()
            ->assertContains('ENVIRONMENT')
            ->assertContains('DATABASE')
            ->assertContains('CACHE')
            ->assertContains('TAILWINDCSS')
            ->assertContains('URL');
    }

    public function test_standard_output_with_alias(): void
    {
        $this->console
            ->call('a')
            ->assertSuccess()
            ->assertContains('ENVIRONMENT')
            ->assertContains('DATABASE')
            ->assertContains('CACHE')
            ->assertContains('TAILWINDCSS')
            ->assertContains('URL');
    }

    public function test_json_output(): void
    {
        $output = $this->console
            ->call('about -json')
            ->assertSuccess()
            ->assertJson()
            ->assertSee('"environment":')
            ->assertSee('"database":')
            ->assertSee('"cache":')
            ->assertSee('"tailwindcss":')
            ->assertSee('"tempest_version":')
            ->assertSee('"php_version":')
            ->assertSee('"composer_version":')
            ->assertSee('"url":')
            ->assertSee('"os":')
            ->assertSee('"engine":')
            ->assertSee('"global_cache":')
            ->assertSee('"discovery_cache":')
            ->assertSee('"config_cache":')
            ->assertSee('"icon_cache":')
            ->assertSee('"view_cache":')
            ->assertSee('"project_cache":')
            ->assertSee('"tailwind_css_version":');
    }

    public function test_json_output_with_alias(): void
    {
        $output = $this->console
            ->call('about -j')
            ->assertSuccess()
            ->assertJson()
            ->assertSee('"environment":')
            ->assertSee('"database":')
            ->assertSee('"cache":')
            ->assertSee('"tailwindcss":')
            ->assertSee('"tempest_version":')
            ->assertSee('"php_version":')
            ->assertSee('"composer_version":')
            ->assertSee('"url":')
            ->assertSee('"os":')
            ->assertSee('"engine":')
            ->assertSee('"global_cache":')
            ->assertSee('"discovery_cache":')
            ->assertSee('"config_cache":')
            ->assertSee('"icon_cache":')
            ->assertSee('"view_cache":')
            ->assertSee('"project_cache":')
            ->assertSee('"tailwind_css_version":');
    }

    public function test_database_types(): void
    {
        // SQLite
        $this->container->singleton(\Tempest\Database\Config\DatabaseConfig::class, fn () => new SQLiteConfig());
        $mockDatabase = $this->createMock(Database::class);
        $mockDatabase->method('fetchFirst')->willReturn(['sqlite_version()' => '3.49.1']);
        $this->container->singleton(Database::class, fn () => $mockDatabase);
        $output = $this->console
            ->call('about')
            ->assertSuccess()
            ->assertSee('Engine')
            ->assertSee('3.49.1');

        // PostgreSQL
        $this->container->singleton(\Tempest\Database\Config\DatabaseConfig::class, fn () => new PostgresConfig());
        $mockDatabase = $this->createMock(Database::class);
        $mockDatabase->method('fetchFirst')->willReturn(['version' => 'PostgreSQL 13.4']);
        $this->container->singleton(Database::class, fn () => $mockDatabase);
        $output = $this->console
            ->call('about')
            ->assertSuccess()
            ->assertSee('Engine')
            ->assertSee('PostgreSQL 13.4');

        // MySQL
        $this->container->singleton(\Tempest\Database\Config\DatabaseConfig::class, fn () => new MysqlConfig());
        $mockDatabase = $this->createMock(Database::class);
        $mockDatabase->method('fetchFirst')->willReturn(['VERSION()' => '8.0.27']);
        $this->container->singleton(Database::class, fn () => $mockDatabase);
        $output = $this->console
            ->call('about')
            ->assertSuccess()
            ->assertSee('Engine')
            ->assertSee('MySQL 8.0.27');
    }

    /**
     * Tests the about command behavior when the database connection fails.
     */
    public function test_database_connection_failure(): void
    {
        // Mock a database that throws an exception on fetchFirst
        $this->container->singleton(\Tempest\Database\Config\DatabaseConfig::class, fn () => new SQLiteConfig());
        $mockDatabase = $this->createMock(Database::class);
        $mockDatabase->method('fetchFirst')->willThrowException(new \Exception('Database connection failed'));
        $this->container->singleton(Database::class, fn () => $mockDatabase);

        // Execute the about command and verify it handles the failure
        $this->console
            ->call('about')
            ->assertSuccess() // Ensure the command still completes (adjust if it should fail)
            ->assertSee('Engine')
            ->assertSee('Unable to connect to database'); // Adjust based on actual error message
    }

    public function test_cache_status(): void
    {
        $output = $this->console
            ->call('about')
            ->assertSuccess()
            ->assertContains('Global Cache ........................................................................................................ ENABLED')
            ->assertContains('Discovery Cache .................................................................................................... DISABLED');

        // Test with CACHE=false and DISCOVERY_CACHE=true
        putenv('CACHE=false');
        putenv('DISCOVERY_CACHE=true');
        $this->console
            ->call('about')
            ->assertSuccess()
            ->assertContains('Global Cache ....................................................................................................... DISABLED')
            ->assertContains('Discovery Cache ..................................................................................................... ENABLED');

        // Test with both disabled
        putenv('CACHE=false');
        putenv('DISCOVERY_CACHE=false');
        $this->console
            ->call('about')
            ->assertSuccess()
            ->assertContains('Global Cache ....................................................................................................... DISABLED')
            ->assertContains('Discovery Cache .................................................................................................... DISABLED');

        // Test with environment variables unset (default behavior)
        putenv('CACHE');
        putenv('DISCOVERY_CACHE');
        $this->console
            ->call('about')
            ->assertSuccess()
            ->assertContains('Global Cache ....................................................................................................... DISABLED') // Adjust based on default
            ->assertContains('Discovery Cache .................................................................................................... DISABLED'); // Adjust based on default
    }

    public function test_tailwind_version(): void
    {
        // Create a temporary directory for the test
        $tempDir = sys_get_temp_dir() . '/tempest_test_' . uniqid();
        mkdir($tempDir);

        // Create a temporary package.json file in this directory
        $tempPackageJson = $tempDir . '/package.json';
        file_put_contents($tempPackageJson, json_encode(['devDependencies' => ['tailwindcss' => '^3.1.8']]));

        // Save the current working directory
        $originalDir = getcwd();
        // Change the working directory for the test
        chdir($tempDir);

        // Execute the about command
        $this->console
            ->call('about')
            ->assertSuccess()
            ->assertContains('Tailwind CSS Version')
            ->assertContains('3.1.8');

        // Restore the original working directory
        chdir($originalDir);

        // Delete the temporary file and directory
        unlink($tempPackageJson);
        rmdir($tempDir);
    }

    public function test_tailwind_version_no_package_json(): void
    {
        // Create a temporary directory for the test to ensure package.json does not exist
        $tempDir = sys_get_temp_dir() . '/tempest_test_' . uniqid();
        mkdir($tempDir);

        // Save the current working directory
        $originalDir = getcwd();
        // Change the working directory to the temporary directory (where package.json does not exist)
        chdir($tempDir);

        // Execute the about command and verify the output
        $this->console
            ->call('about')
            ->assertSuccess()
            ->assertContains('Tailwind CSS Version')
            ->assertContains('package.json not found'); // Ensure this matches the actual output

        // Restore the original working directory
        chdir($originalDir);

        // Clean up by removing the temporary directory
        rmdir($tempDir);
    }

    /**
     * Tests the about command behavior when JSON encoding fails.
     */
    public function test_json_output_encoding_failure(): void
    {
        // Mock the SystemInfoProvider to return invalid data
        $infoProvider = $this->createPartialMock(SystemInfoProvider::class, ['gatherInformation']);
        $infoProvider
            ->method('gatherInformation')
            ->willReturn([
                'environment' => [
                    'Tempest Version' => '1.0.0-beta.1',
                    'Invalid Data' => "\x80\x81", // Invalid UTF-8 sequence
                ],
            ]);

        // Inject the mocked provider into the container
        $this->container->singleton(SystemInfoProvider::class, fn () => $infoProvider);

        // Execute the about command with JSON output
        $this->console
            ->call('about -json')
            ->assertError()
            ->assertSee('Failed to encode JSON'); // Adjust based on actual error message
    }

    /**
     * Tests the execution time of the about command to ensure performance.
     */
    public function test_command_performance(): void
    {
        $startTime = microtime(true);
        $this->console->call('about')->assertSuccess();
        $endTime = microtime(true);

        $executionTime = $endTime - $startTime;
        $this->assertLessThan(1.0, $executionTime, 'The about command took too long to execute');
    }

    /**
     * Tests the help output of the about command.
     */
    public function test_command_help(): void
    {
        $this->console
            ->call('about --help')
            ->assertSuccess()
            ->assertSee('about') // Ensure the command name is shown
            ->assertSee('View a summary of information about your Tempest project') // Ensure description is shown
            ->assertSee('-j, --json'); // Ensure JSON option is documented
    }
}
