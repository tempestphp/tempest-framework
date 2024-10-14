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
        string $classname
    ): void {
        $project_namespace = $this->composer->mainNamespace;
        $pathPrefix        = 'Controllers';
        $classSuffix       = 'Controller';

        // Split namespace and classname
        $fullNamespace = PathHelper::toNamespace($pathPrefix . DIRECTORY_SEPARATOR . $classname);
        $fullNamespace = str($fullNamespace)->finish($classSuffix);
        $namespace     = $fullNamespace->beforeLast('\\')->toString();
        $classname     = $fullNamespace->afterLast('\\')->toString();

        // Create final path and namespace
        $path      = PathHelper::make($project_namespace->path . $namespace);
        $namespace = PathHelper::toNamespace($project_namespace->namespace . $namespace);

        // @TODO refactor above code to only use the required methods ( namespace could be useless before prompting to the user )

        $targetPath = $this->promptTargetPath(
            classname    : $classname,
            suggestedPath: $path . DIRECTORY_SEPARATOR . $classname . '.php',
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
            ->setClassName($classname);

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
