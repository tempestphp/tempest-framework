<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ExitCode;
use Tempest\Console\HasConsole;
use Tempest\Container\Container;

final readonly class AboutCommand
{
    use HasConsole;

    private Container $container;
    private SystemInfoProvider $infoProvider;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->infoProvider = $container->get(SystemInfoProvider::class);
    }

    #[ConsoleCommand(
        name: 'about',
        description: 'View a summary of information about your Tempest project',
        aliases: ['a'],
    )]
    public function __invoke(
        #[ConsoleArgument(description: 'Format to json', aliases: ['-j', '--json'])]
        ?bool $json = null,
    ): ExitCode {
        // Collect all information into a structured array
        $data = $this->infoProvider->gatherInformation();

        // Check if user chose JSON option
        if ($json) {
            // Convert array keys to snake_case
            $snakeCaseData = $this->arrayKeysToSnakeCase($data);
            // Encode the data as JSON
            $jsonOutput = json_encode($snakeCaseData, JSON_PRETTY_PRINT);
            // Check if json_encode failed
            if ($jsonOutput === false) {
                $this->console->error('Failed to encode JSON: ' . json_last_error_msg());
                return ExitCode::ERROR;
            }
            $this->console->writeln($jsonOutput);
        } else {
            // Otherwise, display the data in the standard formatted way
            $this->displayNormalOutput($data);
        }

        return ExitCode::SUCCESS;
    }

    /**
     * Displays the gathered information in the standard console format.
     *
     * @param array $data The structured information to display
     */
    private function displayNormalOutput(array $data): void
    {
        // Display Environment section
        $this->displaySection('Environment', $data['environment']);

        // Display Database section
        $this->displaySection('Database', $data['database']);

        // Display Cache section with styled statuses
        $this->console->header('Cache');
        foreach ($data['cache'] as $key => $value) {
            $this->displayCacheStatus($key, $value);
        }

        // Display TailwindCSS section
        $this->displaySection('TailwindCSS', $data['tailwindcss']);
    }

    /**
     * Displays a standard section with a header and key-value pairs.
     *
     * @param string $sectionName The name of the section to display
     * @param array $sectionData The data for the section
     */
    private function displaySection(string $sectionName, array $sectionData): void
    {
        $this->console->header($sectionName);
        foreach ($sectionData as $key => $value) {
            $this->console->keyValue($key, $value);
        }
    }

    /**
     * Displays the status of a cache in an appropriate style.
     *
     * @param string $label The name of the cache to be displayed.
     * @param string $envVar The corresponding environment variable.
     */
    private function displayCacheStatus(string $label, bool|string|int $value): void
    {
        $isEnabled = $this->infoProvider->isCacheEnabled($value);
        $status = $isEnabled ? "<style='bold fg-green'>ENABLED</style>" : "<style='bold fg-red'>DISABLED</style>";
        $this->console->keyValue($label, $status);
    }

    /**
     * Transform a string in snake case format
     *
     * @param string $string
     * @return string
     */
    private function toSnakeCase(string $string): string
    {
        // Replace spaces by underscores
        $string = str_replace(' ', '_', $string);
        // Convert all characters in lowercase
        $string = strtolower($string);
        return $string;
    }

    /**
     * Convert keys of an array in snake case
     *
     * @param array $array
     * @return array
     */
    private function arrayKeysToSnakeCase(array $array): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            // Convert key in snake_case
            $newKey = $this->toSnakeCase($key);
            if (is_array($value)) {
                // If value is an array, apply recusivity
                $result[$newKey] = $this->arrayKeysToSnakeCase($value);
            } else {
                // Else keep the value as is
                $result[$newKey] = $value;
            }
        }
        return $result;
    }
}
