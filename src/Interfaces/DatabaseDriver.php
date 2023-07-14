<?php

namespace Tempest\Interfaces;

interface DatabaseDriver
{
    public function getDsn(): string;

    public function getUsername(): ?string;

    public function getPassword(): ?string;
}