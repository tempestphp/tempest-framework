<?php

declare(strict_types=1);

namespace Tempest\Mapper\Mappers;

use PDO;
use Tempest\Interfaces\Mapper;

class SqlMapper implements Mapper
{
    public function __construct(private PDO $pdo)
    {
    }

    public function map(string $className, mixed $data): object
    {
        $data = $this->pdo->query($data)->fetchAll();

        dd($data);
    }
}
