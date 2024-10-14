<?php

declare(strict_types=1);

namespace Tempest\Console\Commands\Generators;

use Tempest\Console\Console;
use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\Stubs\ControllerStub;
use Tempest\Core\Composer;
use Tempest\Generation\ClassManipulator;
use Tempest\Support\NamespaceHelper;
use Tempest\Support\PathHelper;
use function Tempest\Support\str;

final class MakeControllerCommand
{
    use HasConsole;

    public function __construct(
        protected readonly Composer $composer,
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

        // Transform stub to class
        $classManipulator = (new ClassManipulator(ControllerStub::class))
            ->setNamespace($namespace)
            ->setClassName($classname);

        // @TODO Find a better way to handle this : maybe use Filesystem or something like that
        // Recursively create directories before writing the file
        if (! is_dir($path)) {
            mkdir($path, recursive: true);
        }

        // Write the file
        file_put_contents(
            $path . DIRECTORY_SEPARATOR . $classname . '.php',
            $classManipulator->print()
        );
    }
}
