<?php

declare(strict_types=1);

namespace Tempest\Auth;

interface Identifiable
{
    public function source(): string;

    public function identifier(): string;

    public function identifierValue(): string;

    public function secret(): string;

    public function secretValue(): string;
}
