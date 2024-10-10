<?php

declare(strict_types=1);

namespace Tempest\Generation;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use ReflectionClass;

final class ClassManipulator
{
    use ManipulatesPhpClasses;

    public function __construct(string|ReflectionClass $className)
    {
        $reflection = is_string($className)
            ? new ReflectionClass($className)
            : $className;

        $this->file = new PhpFile();
        $this->classType = ClassType::from($reflection->getName(), withBodies: true); // @phpstan-ignore-line
        $this->namespace = $reflection->getNamespaceName();
    }
}
