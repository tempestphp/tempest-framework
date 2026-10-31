<?php

declare(strict_types=1);

namespace Tempest\Generation\Php;

final class ClassGenerator
{
    use ManipulatesPhpClasses;

    public function __construct(string $name, string $namespace)
    {
        $this->file = PhpFile::create($namespace);
        $this->class = $this->file->addClass($name);
    }
}
