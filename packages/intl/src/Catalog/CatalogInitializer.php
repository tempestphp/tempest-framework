<?php

namespace Tempest\Intl\Catalog;

use Matecat\XliffParser\XliffParser;
use Symfony\Component\Yaml\Yaml;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Intl\IntlConfig;
use Tempest\Intl\Locale;
use Tempest\Support\Arr;
use Tempest\Support\Filesystem;
use Tempest\Support\Json;
use Tempest\Support\Str;

final class CatalogInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): Catalog
    {
        $config = $container->get(IntlConfig::class);
        $catalog = [];

        foreach ($config->translationMessagePaths as $locale => $paths) {
            $locale = Locale::from($locale)->value;
            $catalog[$locale] ??= [];

            foreach ($paths as $path) {
                $contents = Filesystem\read_file($path);
                $messages = match (true) {
                    Str\ends_with($path, '.json') => Json\decode($contents),
                    Str\ends_with($path, ['.yaml', '.yml']) => Yaml::parse($contents),
                };

                foreach (Arr\dot($messages) as $key => $message) {
                    $catalog[$locale] = Arr\set_by_key($catalog[$locale], $key, $message);
                }
            }
        }

        $xliff = new XliffParser();

        foreach ($config->catalogPaths as $path) {
            $contents = Filesystem\read_file($path);
            $parsed = $xliff->xliffToArray($contents);

            foreach ($parsed['files'] as $file) {
                $locale = Locale::from(str_replace('-', '_', $file['attr']['target-language']))->value;
                $catalog[$locale] ??= [];

                foreach ($file['trans-units'] as $message) {
                    $catalog[$locale] = Arr\set_by_key(
                        array: $catalog[$locale],
                        key: $message['attr']['id'],
                        value: preg_replace('/^\s+/m', '', trim($message['target']['raw-content'][0])),
                    );
                }
            }
        }

        return new GenericCatalog($catalog);
    }
}
