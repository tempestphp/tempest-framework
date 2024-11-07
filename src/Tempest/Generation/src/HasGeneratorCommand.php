<?php

declare(strict_types=1);

namespace Tempest\Generation;

use function Tempest\Support\str;
use Tempest\Validation\Rules\NotEmpty;
use Tempest\Validation\Rules\EndsWith;
use Tempest\Support\PathHelper;
use Tempest\Core\Composer;
use Tempest\Container\Inject;
use Tempest\Console\Console;

/**
 * Provides a bunch of methods to generate files and work with common user input.
 */
trait HasGeneratorCommand
{
    #[Inject]
    protected readonly Console $console;

    #[Inject]
    protected readonly Composer $composer;

    #[Inject]
    protected readonly StubFileGenerator $stubFileGenerator;

    /**
     * Gets a suggested path for the given class name.
     * This will use the user's main namespace as the base path.
     *
     * @param string $className The class name to generate the path for, can include path parts (e.g. 'Models/User').
     * @param string|null $pathPrefix The prefix to add to the path (e.g. 'Models').
     * @param string|null $classSuffix The suffix to add to the class name (e.g. 'Model').
     *
     * @return string The fully suggested path including the filename and extension.
     */
    protected function getSuggestedPath(string $className, ?string $pathPrefix = null, ?string $classSuffix = null): string
    {
        // Separate input path and classname
        $inputClassName = PathHelper::toClassName($className);
        $inputPath = str(PathHelper::make($className))->replace($inputClassName, '')->toString();
        $className = str($inputClassName)
            ->pascal()
            ->finish($classSuffix ?? '')
            ->toString();

        // Prepare the suggested path from the project namespace
        $suggestedPath = str(PathHelper::make(
            $this->composer->mainNamespace->path,
            $pathPrefix ?? '',
            $inputPath,
        ))
            ->finish(DIRECTORY_SEPARATOR)
            ->append($className . '.php')
            ->toString();

        return $suggestedPath;
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
        $className = PathHelper::toClassName($suggestedPath);

        return $this->console->ask(
            question  : sprintf('Where do you want to save the file "%s"?', $className),
            default   : $suggestedPath,
            validation: [new NotEmpty(), new EndsWith('.php')],
        );
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
        if (! file_exists($targetPath)) {
            return true;
        }

        return $this->console->confirm(
            question: sprintf('The file "%s" already exists. Do you want to override it?', $targetPath),
            default : false,
        );
    }
}
