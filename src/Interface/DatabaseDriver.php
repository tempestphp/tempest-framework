<?php

declare(strict_types=1);

namespace Tempest\Interface;

interface DatabaseDriver
{
    public function getDsn(): string;

    public function getUsername(): ?string;

    public function getPassword(): ?string;
}
