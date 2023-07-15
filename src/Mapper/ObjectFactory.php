<?php

namespace Tempest\Mapper;

use _PHPStan_690619d82\Nette\PhpGenerator\ClassType;
use Tempest\Mapper\Mappers\SqlMapper;

/* @template ClassType */
final class ObjectFactory
{
    private string $className;

    public function __construct(
        private readonly SqlMapper $sqlMapper,
    ) {
    }

    /**
     * @template InputClassType
     * @param class-string<InputClassType> $className
     * @return self<InputClassType>
     */
    public function className(string $className): self
    {
        $this->className = $className;

        return $this;
    }

    /**
     * @return ClassType
     */
    public function from(mixed $data): object
    {
        return $this->sqlMapper->map($this->className, $data);
    }
}
