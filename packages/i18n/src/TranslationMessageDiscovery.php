<?php

declare(strict_types=1);

namespace Tempest\Internationalization;

use Tempest\Discovery\DiscoversPath;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;
use Tempest\Support\Language\Locale;

use function Tempest\Support\arr;
use function Tempest\Support\str;
use function Tempest\Support\Str\ends_with;

final class TranslationMessageDiscovery implements Discovery, DiscoversPath
{
    use IsDiscovery;

    public function __construct(
        private readonly InternationalizationConfig $config,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        return;
    }

    public function discoverPath(DiscoveryLocation $location, string $path): void
    {
        if (! ends_with($path, '.json')) {
            return;
        }

        if (! $this->isLocale($locale = str($path)->beforeLast('.')->afterLast('.')->toString())) {
            return;
        }

        if (! is_file($path)) {
            return;
        }

        $this->discoveryItems->add($location, [$path, $locale]);
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as [$path, $locale]) {
            $this->config->addTranslationMessageFile(Locale::from($locale), $path);
        }
    }

    private function isLocale(string $candidate): bool
    {
        $locale = arr(Locale::cases())
            ->first(function (Locale $locale) use ($candidate) {
                if (strtolower($locale->value) === strtolower($candidate)) {
                    return true;
                }

                return strtolower($locale->getLanguage()) === strtolower($candidate);
            });

        return ! is_null($locale);
    }
}
