<?php

namespace Tempest\HttpApi;

use Tempest\Support\Str\ImmutableString;

trait HasResourceAttributes
{
    use HasResourceRecord;

    public static function getResourceUriName(): string
    {
        $resourceName = (string) new ImmutableString(static::getResourceRecord())
            ->afterLast('\\')
            ->kebab();

        // TODO: Consider having a config option to set the pluralizer
        return new \Tempest\Support\Pluralizer\InflectorPluralizer()->pluralize($resourceName);
    }

    public static function getResourceApiVersion(): ?string
    {
        return 'v1';
    }

    public static function getResourcePagination(): ?Pagination
    {
        return new OffsetPagination();
    }

    public static function getResourceSearchableColumns(): ?array
    {
        return ['name', 'email'];
    }
}
