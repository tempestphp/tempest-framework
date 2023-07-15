<?php

declare(strict_types=1);

namespace Tempest\ORM;

use _PHPStan_690619d82\Nette\PhpGenerator\ClassType;
use Tempest\ORM\Mappers\ArrayMapper;

/* @template ClassType */
final class ObjectFactory
{
    private string $className;

    public function __construct(
        private readonly ArrayMapper $arrayMapper,
    ) {
    }

    /**
     * @template InputClassType
     * @param class-string<InputClassType> $className
     * @return self<InputClassType>
     */
    public function forClass(string $className): self
    {
        $this->className = $className;

        return $this;
    }

    /**
     * @return ClassType
     */
    public function from(mixed $data): object
    {
        // TODO: dynamic mappers
        return $this->arrayMapper->map($this->className, $data);
    }
}
