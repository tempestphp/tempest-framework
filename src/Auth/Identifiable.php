<?php

declare(strict_types=1);

namespace Tempest\Auth;

interface Identifiable
{
    public function source(): string;

    public function identifierField(): string;

    public function identifierValue(): string;

    public function secretField(): string;

    public function secretValue(): string;

    public function setSecret(string $secret): static;

    public function setCredentials(string $identifier, string $secret): static;
}
