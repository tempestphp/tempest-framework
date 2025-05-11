<?php

namespace Tempest\Console\Commands;

use Tempest\Container\Container;
use Tempest\Core\Kernel;
use Tempest\Database\Config\DatabaseConfig;
use Tempest\Database\Config\MysqlConfig;
use Tempest\Database\Config\PostgresConfig;
use Tempest\Database\Config\SQLiteConfig;
use Tempest\Database\Database;
use Tempest\Database\Query;

class SystemInfoProvider
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function gatherInformation(): array
    {
        return [
            'environment' => [
                'Tempest Version' => Kernel::VERSION,
                'PHP Version' => PHP_VERSION,
                'Composer Version' => $this->getComposerVersion(),
                'Environment' => getenv('ENVIRONMENT') ?: 'local',
                'URL' => getenv('BASE_URI') ?: 'Not set',
                'OS' => $this->getOSInfo(),
            ],
            'database' => [
                'Engine' => $this->getDatabaseType($this->container),
            ],
            'cache' => [
                'Global Cache' => $this->isCacheEnabled(getenv('CACHE')),
                'Discovery Cache' => $this->isCacheEnabled(getenv('DISCOVERY_CACHE')),
                'Config Cache' => $this->isCacheEnabled(getenv('CONFIG_CACHE')),
                'Icon Cache' => $this->isCacheEnabled(getenv('ICON_CACHE')),
                'View Cache' => $this->isCacheEnabled(getenv('VIEW_CACHE')),
                'Project Cache' => $this->isCacheEnabled(getenv('PROJECT_CACHE')),
            ],
            'tailwindcss' => [
                'Tailwind CSS Version' => $this->getTailwindVersion(),
            ],
        ];
    }

    /**
     * Get composer version
     *
     * @return string composer version (for example 2.8.8) or error message
     */
    private function getComposerVersion(): string
    {
        // Check if shell_exec is available
        if (! function_exists('shell_exec')) {
            return 'shell_exec disabled';
        }

        // Redirect stderr to stdout to capture everything
        $output = shell_exec('composer --version 2>&1');
        if (! $output) {
            return 'Composer not installed';
        }

        // Split the output into lines and get the version number via regex
        $lines = explode("\n", $output);
        foreach ($lines as $line) {
            if (str_starts_with($line, 'Composer version')) {
                if (preg_match('/Composer version (\S+)/', $line, $matches)) {
                    return $matches[1];
                }
            }
        }

        return 'Unknown';
    }

    /**
     * Determines the active database type and version in the Tempest project.
     *
     * @param Container $container The dependency container of Tempest
     * @return string The active database type and version (e.g., "SQLite 3.35.5", "PostgreSQL 13.4", "MySQL 8.0.27")
     */
    private function getDatabaseType(Container $container): string
    {
        // Retrieve the database configuration from the container
        $databaseConfig = $this->container->get(DatabaseConfig::class);

        // Determine the database type and version query based on the config
        if ($databaseConfig instanceof SQLiteConfig) {
            $type = 'SQLite';
            $versionQuery = 'SELECT sqlite_version();';
        } elseif ($databaseConfig instanceof PostgresConfig) {
            $type = 'PostgreSQL';
            $versionQuery = 'SELECT version();';
        } elseif ($databaseConfig instanceof MysqlConfig) {
            $type = 'MySQL';
            $versionQuery = 'SELECT VERSION();';
        } else {
            return 'Unknown';
        }

        // Check if DatabaseConnection is registered in the container
        if ($this->container->get(DatabaseConfig::class)) {
            try {
                // Get the database connection and query the version
                $connection = $this->container->get(Database::class);
                $result = $connection->fetchFirst(new Query($versionQuery));

                // SQLite
                if ($type === 'SQLite') {
                    $version = $result['sqlite_version()'];
                }

                // MySQL
                if ($type === 'MySQL') {
                    $version = $result['VERSION()'];
                }

                // For PostgreSQL, extract the version from the full string
                if ($type === 'PostgreSQL') {
                    preg_match('/PostgreSQL (\S+)/', $result['version'], $matches);
                    $version = $matches[1] ?? 'Unknown';
                }

                return $type . ' ' . $version;
            } catch (\Exception $e) {
                // Handle any query or connection errors
                return 'Unable to connect to database';
            }
        } else {
            // If DatabaseConnection isn’t available, return the type only
            return $type . ' (Connection unavailable)';
        }
    }

    /**
     * Get the Tailwind version
     *
     * @return string Tailwind version (for example 4.1.0) or error message
     */
    private function getTailwindVersion(): string
    {
        // Path to the package.json file
        $packageJsonPath = 'package.json';

        // Check if the file exists
        if (! file_exists($packageJsonPath)) {
            return 'package.json not found';
        }

        // Read the file content
        $packageJsonContent = file_get_contents($packageJsonPath);
        if ($packageJsonContent === false) {
            return 'Unable to read package.json';
        }

        // Decode the JSON content into an associative array
        $packageData = json_decode($packageJsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return 'Invalid JSON in package.json';
        }

        // Check for tailwindcss in dependencies or devDependencies
        $dependencies = $packageData['dependencies'] ?? [];
        $devDependencies = $packageData['devDependencies'] ?? [];

        // Get the tailwindcss version
        if (isset($dependencies['tailwindcss'])) {
            $version = $dependencies['tailwindcss'];
        } elseif (isset($devDependencies['tailwindcss'])) {
            $version = $devDependencies['tailwindcss'];
        } else {
            return 'Tailwind CSS not installed';
        }

        // Remove the caret (^) if present
        $cleanVersion = ltrim($version, '^');

        return $cleanVersion;
    }

    /**
     * Retrieves the operating system name and version.
     *
     * For Windows, it maps the version number to a readable name (e.g., "Windows 10").
     * For Linux, it attempts to retrieve the distribution name using `lsb_release`.
     * For macOS, it returns the OS name with the version number.
     *
     * @return string The OS name and version (e.g., "Windows 10", "Ubuntu 20.04.3 LTS", "Mac 12.6.0")
     */
    private function getOSInfo(): string
    {
        $osName = php_uname('s'); // Get the operating system name
        $osVersion = php_uname('r'); // Get the operating system release version

        // Normalize OS name and handle specific cases
        if (stripos($osName, 'Darwin') !== false) {
            // If Darwin, get the marketing version number
            $macOSVersion = shell_exec('sw_vers -productVersion');
            if ($macOSVersion) {
                $macOSVersion = trim($macOSVersion); // remove spaces or carret return
                return 'macOS ' . $macOSVersion; // Return for example "macOS 15.3.2"
            } else {
                // If fail, go back to the kernel version number
                $osVersion = php_uname('r');
                return 'macOS (Kernel ' . $osVersion . ')'; // Return "Mac 24.3.0"
            }
        } elseif (stripos($osName, 'Windows') !== false) {
            // Simple mapping for Windows versions based on release number
            $versionMap = [
                '5.1' => 'Windows XP',
                '5.2' => 'Windows XP', // Could also be Windows Server 2003
                '6.0' => 'Windows Vista',
                '6.1' => 'Windows 7',
                '6.2' => 'Windows 8',
                '6.3' => 'Windows 8.1',
                '10.0' => 'Windows 10', // Note: Windows 11 also reports 10.0, distinction requires build number
            ];

            $majorMinor = substr($osVersion, 0, strcspn($osVersion, ' ')); // Extract major.minor version
            return $versionMap[$majorMinor] ?? ('Windows ' . $osVersion); // Map or fallback to version
        } elseif (stripos($osName, 'Linux') !== false) {
            if (function_exists('shell_exec')) {
                // Attempt to get Linux distribution details
                $output = shell_exec('lsb_release -a 2>/dev/null');
                if ($output) {
                    $lines = explode("\n", $output);
                    foreach ($lines as $line) {
                        if (str_starts_with($line, 'Description:')) {
                            $description = trim(substr($line, strlen('Description:')));
                            return $description; // Return distribution name, e.g., "Ubuntu 20.04.3 LTS"
                        }
                    }
                }
            }
            // Fallback to generic Linux with kernel version if distribution info unavailable
            return 'Linux ' . $osVersion;
        } else {
            return 'Unknown OS'; // Handle unrecognized OS
        }
    }

    /**
     * Determines whether a cache is activated according to the value of the environment variable.
     *
     * @param string|null $value The value of the environment variable.
     * @return bool True if the cache is enabled, false otherwise.
     */
    public function isCacheEnabled(string|bool|int $value): bool
    {
        if (is_null($value)) {
            return false;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            $lowerValue = strtolower(trim($value)); // Nettoie la chaîne
            return in_array($lowerValue, ['true', '1', 'on'], true);
        }

        return false;
    }
}
