<?php

namespace Tempest\Intl\Catalog;

use Tempest\Intl\IntlConfig;
use Tempest\Intl\Locale;
use Tempest\Support\Arr;

final class GenericCatalog implements Catalog
{
    /**
     * @var array<string,string[]> $catalog
     */
    public function __construct(
        private array $catalog = [],
    ) {}

    public function has(Locale $locale, string $key): bool
    {
        return Arr\has_key($this->catalog, "{$locale->value}.{$key}");
    }

    public function get(Locale $locale, string $key): ?string
    {
        return Arr\get_by_key(
            array: $this->catalog,
            key: "{$locale->value}.{$key}",
            default: Arr\get_by_key(
                array: $this->catalog,
                key: "{$locale->getLanguage()}.{$key}",
            ),
        );
    }

    public function add(Locale $locale, string $key, string $message): self
    {
        $this->catalog = Arr\set_by_key($this->catalog, "{$locale->value}.{$key}", $message);

        return $this;
    }
}
