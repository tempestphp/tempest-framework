<?php

declare(strict_types=1);

namespace Tempest\Console\Commands\Generators;

use function Tempest\Support\str;
use Tempest\Validation\Rules\NotEmpty;
use Tempest\Validation\Rules\EndsWith;
use Tempest\Support\PathHelper;
use Tempest\Generation\ClassManipulator;
use Tempest\Core\Composer;
use Tempest\Console\Stubs\ControllerStub;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleArgument;
use Tempest\Console\Console;
use Tempest\Support\StringHelper;

final class MakeControllerCommand
{
    public function __construct(
        protected readonly Composer $composer,
        protected readonly Console $console,
    ) {
    }

    #[ConsoleCommand(
        name       : 'make:controller',
        description: 'Create a new controller class with a basic route',
        aliases    : ['controller:make', 'controller:create', 'create:controller'],
    )]
    public function __invoke(
        #[ConsoleArgument(
            help: 'The name of the controller class to create ( "Controller" will be suffixed )',
        )]
        string $controllerClassname,
        ?string $controllerPath = null,
        ?string $controllerView = null,
    ): void {
        $pathPrefix  = 'Controllers';
        $classSuffix = 'Controller';

        // Separate input path and classname
        $classname = PathHelper::toClassName($controllerClassname);
        $inputPath = str(PathHelper::make($controllerClassname))->replace($classname, '')->toString();
        $classname = str($classname)
            ->pascal()
            ->finish($classSuffix)
            ->toString();

        // Prepare the suggested path from the project namespace
        $suggestedPath = str(PathHelper::make(
            $this->composer->mainNamespace->path,
            $pathPrefix,
            $inputPath,
        ))
            ->finish(DIRECTORY_SEPARATOR)
            ->toString();

        $targetPath = $this->promptTargetPath(
            classname    : $classname,
            suggestedPath: $suggestedPath . $classname . '.php',
            rules        : [new NotEmpty(), new EndsWith('.php')]
        );

        if (! $this->prepareFilesystem($targetPath)) {
            $this->console->error('The operation has been aborted.');
            return;
        }

        // Transform stub to class
        $namespace        = PathHelper::toRegisteredNamespace($targetPath);
        $classname        = PathHelper::toClassName($targetPath);
        $classManipulator = (new ClassManipulator(ControllerStub::class))
            ->setNamespace($namespace)
            ->setClassName($classname)
            ->manipulate(fn( StringHelper $code ) => ! is_null( $controllerPath ) ? $code->replace('dummy-path', $controllerPath) : $code)
            ->manipulate(fn( StringHelper $code ) => ! is_null( $controllerView ) ? $code->replace('dummy-view', $controllerView) : $code);

        // Write the file
        file_put_contents(
            $targetPath,
            $classManipulator->print()
        );

        $this->console->success(sprintf('Controller successfully created at "%s".', $targetPath));
    }

    /**
     * Prompt the target path to the user.
     *
     * @param string $classname The name of the class.
     * @param string $suggestedPath The suggested path.
     * @param array $rules The validation rules.
     *
     * @return string The validated target path.
     */
    protected function promptTargetPath(string $classname, string $suggestedPath, array $rules = []): string
    {
        return $this->console->ask(
            question  : sprintf('Where do you want to save the controller "%s"?', $classname),
            default   : $suggestedPath,
            validation: $rules
        );
    }

    /**
     * Prepare the directory structure for the new file.
     * It can also ask the user if they want to overwrite the file if it already exists.
     *
     * @param string $targetPath The path to the target file.
     *
     * @return boolean Whether the filesystem is ready to write the file.
     */
    protected function prepareFilesystem(string $targetPath): bool
    {
        // Maybe delete the file if it exists and we force the override
        if (file_exists($targetPath)) {
            $shouldOverride = $this->console->confirm(
                question: sprintf('"%s" already exists, do you want to overwrite it?', $targetPath),
                default : false,
            );

            if (! $shouldOverride) {
                return false;
            }

            @unlink($targetPath);
        }

        // Recursively create directories before writing the file
        if (! file_exists(dirname($targetPath))) {
            mkdir(dirname($targetPath), recursive: true);
        }

        return true;
    }
}
