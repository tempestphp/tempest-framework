<?php

declare(strict_types=1);

namespace Tempest\Http\Static;

use Tempest\Core\Discovery;
use Tempest\Core\DiscoveryLocation;
use Tempest\Core\IsDiscovery;
use Tempest\Http\StaticPage;
use Tempest\Reflection\ClassReflector;

final class StaticPageDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly StaticPageConfig $staticPageConfig,
    ) {
    }

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        foreach ($class->getPublicMethods() as $method) {
            $staticPage = $method->getAttribute(StaticPage::class);

            if (! $staticPage) {
                continue;
            }

            $this->discoveryItems->add($location, [$staticPage, $method]);
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as [$staticPage, $method]) {
            $this->staticPageConfig->addHandler($staticPage, $method);
        }
    }
}
