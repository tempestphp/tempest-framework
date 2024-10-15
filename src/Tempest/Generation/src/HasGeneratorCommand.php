<?php

declare(strict_types=1);

namespace Tempest\Generation;

use Tempest\Core\Composer;
use Tempest\Console\Console;

/**
 * This provide bunch of methods to generate files.
 * Also methods to manipulate the user generic input like the class name.
 */
trait HasGeneratorCommand
{
    public function __construct(
        protected readonly Console $console,
        protected readonly Composer $composer,
    ) {}
    
    /**
     * Get a suggested path for the given class name.
     * This will find the main autoloaded namespace in the project's composer file and use it as the base path.
     *
     * @param string $className The class name to generate the path for, can include path parts (e.g. 'Models/User').
     * @param string|null $pathPrefix The prefix to add to the path (e.g. 'Models').
     * @param string|null $classSuffix The suffix to add to the class name (e.g. 'Model').
     *
     * @return string The fully suggested path including the filename and extension.
     */
    protected function getSuggestedPath(string $className, ?string $pathPrefix = null, ?string $classSuffix = null): string
    {
        // @TODO
    }

    /**
     * Get the class name from the suggested path.
     *
     * @param string $suggestedPath The suggested path to extract the class name from.
     *
     * @return string The extracted class name.
     */
    protected function getClassName(string $suggestedPath): string
    {
        // @TODO
    }

    /**
     * Prompt the user for the target path to save the generated file.
     *
     * @param string $suggestedPath The suggested path to show to the user.
     *
     * @return string The target path that the user has chosen.
     */
    protected function promptTargetPath(string $suggestedPath): string
    {
        // @TODO
    }

    /**
     * Ask the user if they want to override the file if it already exists.
     *
     * @param string $targetPath The target path to check for existence.
     *
     * @return bool Whether the user wants to override the file.
     */
    protected function askForOverride(string $targetPath): bool
    {
        // @TODO
    }
}
