<?php

declare(strict_types=1);

namespace Tempest\Database\Drivers;

use SensitiveParameter;
use Tempest\Interfaces\DatabaseDriver;

final readonly class MySqlDriver implements DatabaseDriver
{
    public function __construct(
        #[SensitiveParameter] public string $host = 'localhost',
        #[SensitiveParameter] public string $port = '3306',
        #[SensitiveParameter] public string $username = 'root',
        #[SensitiveParameter] public string $password = '',
        #[SensitiveParameter] public string $database = 'app',
    ) {
    }

    public function getDsn(): string
    {
        return "mysql:host={$this->host};port={$this->port};dbname={$this->database}";
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
}
