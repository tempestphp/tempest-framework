<?php

declare(strict_types=1);

namespace Tempest\Filesystem;

use Stringable;
use UnexpectedValueException;

// TODO: This file is experimental and a WIP.
final class Permissions implements Stringable
{
    private float|int $value;

    public function __construct(string $permissions)
    {
        $this->setOctal($permissions);

        $this->validate();
    }

    public function __toString(): string
    {
        return sprintf('%04o', $this->value);
    }

    private function setOctal(string $value): void
    {
        //        $value = substr(ltrim($value, '0'), -4);
        $this->value = @octdec($value);
    }

    private function validate(): void
    {
        if (preg_match('/^[0-7]{3}$/', (string) $this->value) === false) {
            throw new UnexpectedValueException();
        }
    }
}
