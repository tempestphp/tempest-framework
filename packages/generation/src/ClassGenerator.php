<?php

declare(strict_types=1);

namespace Tempest\Generation;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;

final class ClassGenerator
{
    use ManipulatesPhpClasses;

    public function __construct(string $name, string $namespace)
    {
        $this->file = new PhpFile();
        $this->classType = new ClassType($name);
        $this->namespace = $namespace;
    }
}
