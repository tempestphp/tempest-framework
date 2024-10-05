<?php

namespace Tempest\Filesystem;

use UnexpectedValueException;

final class Permissions
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

    private function setInteger(int $value): void
    {
        if (strlen($value) !== 3) {
            $value = $value & 0777;
        }

        $this->value = $value;
    }

    private function validate(): void
    {
        if (preg_match('/^[0-7]{3}$/', $this->value) === false) {
            throw new UnexpectedValueException();
        }
    }
}