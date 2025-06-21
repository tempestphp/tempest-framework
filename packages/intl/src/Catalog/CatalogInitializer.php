<?php

namespace Tempest\Intl\Catalog;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Intl\InternationalizationConfig;
use Tempest\Intl\Locale;
use Tempest\Support\Arr;
use Tempest\Support\Filesystem;
use Tempest\Support\Json;

final class CatalogInitializer implements Initializer
{
    public function initialize(Container $container): Catalog
    {
        $config = $container->get(InternationalizationConfig::class);
        $catalog = [];

        foreach ($config->translationMessagePaths as $locale => $paths) {
            $locale = Locale::from($locale)->value;
            $catalog[$locale] ??= [];

            foreach ($paths as $path) {
                $messages = Json\decode(Filesystem\read_file($path));
                $messages = Arr\undot($messages);

                foreach ($messages as $key => $message) {
                    $catalog[$locale][$key] = $message;
                }
            }
        }

        return new GenericCatalog($catalog);
    }
}
