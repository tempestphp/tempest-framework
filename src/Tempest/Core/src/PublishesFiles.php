<?php

declare(strict_types=1);

namespace Tempest\Core;

use Closure;
use Exception;
use Tempest\Console\Exceptions\ConsoleException;
use Tempest\Console\HasConsole;
use Tempest\Container\Inject;
use Tempest\Discovery\DoNotDiscover;
use Tempest\Generation\ClassManipulator;
use Tempest\Generation\DataObjects\StubFile;
use Tempest\Generation\Enums\StubFileType;
use Tempest\Generation\Exceptions\FileGenerationAbortedException;
use Tempest\Generation\Exceptions\FileGenerationFailedException;
use Tempest\Generation\StubFileGenerator;
use Tempest\Reflection\FunctionReflector;
use Tempest\Support\Str\ImmutableString;
use Tempest\Validation\Rules\EndsWith;
use Tempest\Validation\Rules\NotEmpty;
use Throwable;

use function strlen;
use function Tempest\root_path;
use function Tempest\Support\Namespace\to_base_class_name;
use function Tempest\Support\path;
use function Tempest\Support\Path\to_absolute_path;
use function Tempest\Support\Path\to_relative_path;
use function Tempest\Support\str;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;

/**
 * Provides a bunch of methods to publish and generate files and work with common user input.
 */
trait PublishesFiles
{
    use HasConsole;

    #[Inject]
    private readonly Composer $composer;

    #[Inject]
    private readonly StubFileGenerator $stubFileGenerator;

    private array $publishedFiles = [];

    private array $publishedClasses = [];

    /**
     * Publishes a file from a source to a destination.
     * @param string $source The path to the source file.
     * @param string $destination The path to the destination file.
     * @param Closure(string $source, string $destination): void|null $callback A callback to run after the file is published.
     */
    public function publish(string $source, string $destination, ?Closure $callback = null): string|false
    {
        try {
            if (! $this->console->confirm(
                question: sprintf('Do you want to create <file="%s" />?', $this->friendlyFileName($destination)),
                default: true,
            )) {
                throw new FileGenerationAbortedException('Skipped.');
            }

            if (! $this->askForOverride($destination)) {
                throw new FileGenerationAbortedException('Skipped.');
            }

            $stubFile = StubFile::from($source);

            // Handle class files
            if ($stubFile->type === StubFileType::CLASS_FILE) {
                $oldClass = new ClassManipulator($source);

                $this->stubFileGenerator->generateClassFile(
                    stubFile: $stubFile,
                    targetPath: $destination,
                    shouldOverride: true,
                    manipulations: [
                        fn (ClassManipulator $class) => $class->removeClassAttribute(DoNotDiscover::class),
                    ],
                );

                $newClass = new ClassManipulator($destination);

                $this->publishedClasses[$oldClass->getClassName()] = $newClass->getClassName();
            }

            // Handle raw files
            if ($stubFile->type === StubFileType::RAW_FILE) {
                $this->stubFileGenerator->generateRawFile(
                    stubFile: $stubFile,
                    targetPath: $destination,
                    shouldOverride: true,
                );
            }

            $this->publishedFiles[] = $destination;

            if ($callback !== null) {
                $callback($source, $destination);
            }

            return $destination;
        } catch (FileGenerationAbortedException) {
            return false;
        } catch (Throwable $throwable) {
            if ($throwable instanceof ConsoleException) {
                throw $throwable;
            }

            throw new FileGenerationFailedException(
                message: 'The file could not be published.',
                previous: $throwable,
            );
        }
    }

    /**
     * Publishes the imports of the published classes.
     * Any published class that is imported in another published class will have its import updated.
     */
    public function publishImports(): void
    {
        foreach ($this->publishedFiles as $file) {
            $contents = str(file_get_contents($file));

            foreach ($this->publishedClasses as $old => $new) {
                $contents = $contents->replace($old, $new);
            }

            file_put_contents($file, $contents);
        }
    }

    /**
     * Gets a suggested path for the given class name.
     * This will use the user's main namespace as the base path.
     * @param string $className The class name to generate the path for, can include path parts (e.g. 'Models/User').
     * @param string|null $pathPrefix The prefix to add to the path (e.g. 'Models').
     * @param string|null $classSuffix The suffix to add to the class name (e.g. 'Model').
     * @return string The fully suggested path including the filename and extension.
     */
    public function getSuggestedPath(string $className, ?string $pathPrefix = null, ?string $classSuffix = null): string
    {
        // Separate input path and classname
        $inputClassName = to_base_class_name($className);
        $inputPath = path($className)->stripEnd($inputClassName);
        $className = str($inputClassName)
            ->pascal()
            ->finish($classSuffix ?? '')
            ->toString();

        // Prepare the suggested path from the project namespace
        return str(to_absolute_path($this->composer->mainNamespace->path, $pathPrefix ?? '', $inputPath))
            ->finish('/')
            ->append($className . '.php')
            ->toString();
    }

    /**
     * Prompt the user for the target path to save the generated file.
     * @param string $suggestedPath The suggested path to show to the user.
     * @return string The target path that the user has chosen.
     */
    public function promptTargetPath(string $suggestedPath): string
    {
        $className = to_base_class_name($suggestedPath);

        return $this->console->ask(
            question: sprintf('Where do you want to save the file <em>%s</em>?', $className),
            default: $suggestedPath,
            validation: [new NotEmpty(), new EndsWith('.php')],
        );
    }

    /**
     * Ask the user if they want to override the file if it already exists.
     * @param string $targetPath The target path to check for existence.
     * @return bool Whether the user wants to override the file.
     */
    public function askForOverride(string $targetPath): bool
    {
        if (! file_exists($targetPath)) {
            return true;
        }

        return $this->console->confirm(
            question: sprintf('The file <file="%s" /> already exists. Do you want to override it?', $this->friendlyFileName($targetPath)),
        );
    }

    /**
     * Updates the contents of a file at the given path.
     *
     * @param string $path The absolute path to the file to update.
     * @param Closure(string|ImmutableString $contents): mixed $callback A callback that accepts the file contents and must return updated contents.
     * @param bool $ignoreNonExisting Whether to throw an exception if the file does not exist.
     */
    public function update(string $path, Closure $callback, bool $ignoreNonExisting = false): void
    {
        if (! is_file($path)) {
            if ($ignoreNonExisting) {
                return;
            }

            throw new Exception("The file at path [{$path}] does not exist.");
        }

        $contents = file_get_contents($path);

        $reflector = new FunctionReflector($callback);
        $type = $reflector->getParameters()->current()->getType();

        $contents = match (true) {
            is_null($type), $type->equals(ImmutableString::class) => (string) $callback(new ImmutableString($contents)),
            $type->accepts('string') => (string) $callback($contents),
            default => throw new Exception('The callback must accept a string or ImmutableString.'),
        };

        file_put_contents($path, $contents);
    }

    /**
     * Updates a JSON file, preserving indentation.
     *
     * @param string $path The absolute path to the file to update.
     * @param Closure(array): array $callback
     * @param bool $ignoreNonExisting Whether to throw an exception if the file does not exist.
     */
    public function updateJson(string $path, Closure $callback, bool $ignoreNonExisting = false): void
    {
        $this->update(
            $path,
            function (string $content) use ($callback) {
                $indent = $this->detectIndent($content);

                $json = json_decode($content, associative: true);
                $json = $callback($json);

                // PHP will output empty arrays for empty dependencies,
                // which is invalid and will make package managers crash.
                foreach (['dependencies', 'devDependencies', 'peerDependencies'] as $key) {
                    if (isset($json[$key]) && ! $json[$key]) {
                        unset($json[$key]);
                    }
                }

                $content = preg_replace_callback(
                    '/^ +/m',
                    fn ($m) => str_repeat($indent, strlen($m[0]) / 4),
                    json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                );

                return "{$content}\n";
            },
            $ignoreNonExisting,
        );
    }

    private function friendlyFileName(string $path): string
    {
        return to_relative_path(root_path(), $path);
    }

    private function detectIndent(string $raw): string
    {
        try {
            return explode('"', explode("\n", $raw)[1])[0] ?: '';
        } catch (Throwable) {
            return '    ';
        }
    }
}
