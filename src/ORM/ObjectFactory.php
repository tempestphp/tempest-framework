<?php

declare(strict_types=1);

namespace Tempest\ORM;

use Tempest\ORM\Exceptions\CannotMapDataException;
use Tempest\ORM\Mappers\ArrayMapper;
use Tempest\ORM\Mappers\SqlMapper;

/* @template ClassType */
final class ObjectFactory
{
    private string $className;

    /** @var \Tempest\Interfaces\Mapper[] */
    private readonly array $mappers;

    public function __construct()
    {
        $arrayMapper = new ArrayMapper();

        $this->mappers = [
            $arrayMapper,
            new SqlMapper(),
        ];
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
     * @return self<ClassType[]>
     */
    public function collection(): self
    {
        return $this;
    }

    /**
     * @return ClassType
     */
    public function from(mixed $data): array|object
    {
        foreach ($this->mappers as $mapper) {
            if ($mapper->canMap($data)) {
                return $mapper->map($this->className, $data);
            }
        }

        throw new CannotMapDataException();
    }
}
