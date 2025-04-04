<?php

namespace Tempest\View\Elements;

use function Tempest\Support\str;

final readonly class RawConditionalAttribute
{
    public function __construct(
        private string $name,
        private string $value,
    ) {}

    public function compile(): string
    {
        return sprintf(
            "<?= %s::render(%s, '%s', %s) ?>",
            self::class,
            '$' . str($this->name)->camel()->toString(),
            $this->name,
            str($this->value)->afterFirst('<?=')->beforeLast('?>')->trim(),
        );
    }

    public static function render(mixed $condition, string $name, string $value): string
    {
        if ($condition === true) {
            return str($name)->kebab()->toString();
        }

        if ($condition) {
            return sprintf('%s="%s"', str($name)->kebab(), $value);
        }
        return '';
    }
}
