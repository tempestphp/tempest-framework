<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Database\Tables\NamingStrategy;

interface DatabaseConnection
{
    public function getDsn(): string;

    public function getUsername(): ?string;

    public function getPassword(): ?string;

    public function dialect(): DatabaseDialect;

    public function tableNamingStrategy(): NamingStrategy;
}
