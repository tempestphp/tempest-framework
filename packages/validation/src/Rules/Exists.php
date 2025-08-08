<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use InvalidArgumentException;
use Tempest\Database\Id;
use Tempest\Validation\Rule;

use function Tempest\Database\query;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Exists implements Rule
{
    public function __construct(
        private string $model,
    ) {
        if (! class_exists($this->model)) {
            throw new InvalidArgumentException("Model {$this->model} does not exist");
        }
    }

    public function isValid(mixed $value): bool
    {
        if ((! is_numeric($value) || is_float($value)) && ! is_object($value)) {
            return false;
        }

        $id = is_object($value) ? $value : new Id($value);

        if ($id->id >= PHP_INT_MAX) {
            return false;
        }

        $model = query($this->model)
            ->select()
            ->get(id: $id);

        return $model !== null;
    }

    public function message(): string
    {
        return sprintf('Record for model %1$s does not exist', $this->model);
    }
}
