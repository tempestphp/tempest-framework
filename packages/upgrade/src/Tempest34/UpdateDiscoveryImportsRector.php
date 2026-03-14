<?php

namespace Tempest\Upgrade\Tempest34;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\UseItem;
use Rector\Rector\AbstractRector;

final class UpdateDiscoveryImportsRector extends AbstractRector
{
    private const array CLASS_RENAMES = [
        'Tempest\Core\DiscoveryCache' => 'Tempest\Discovery\DiscoveryCache',
        'Tempest\Core\DiscoveryCacheStrategy' => 'Tempest\Discovery\DiscoveryCacheStrategy',
        'Tempest\Core\Composer' => 'Tempest\Discovery\Composer',
        'Tempest\Core\ComposerJsonCouldNotBeLocated' => 'Tempest\Discovery\ComposerJsonCouldNotBeLocated',
        'Tempest\Core\DiscoveryCachingStrategyWasChanged' => 'Tempest\Discovery\DiscoveryCachingStrategyWasChanged',
        'Tempest\Core\DiscoveryConfig' => 'Tempest\Discovery\DiscoveryConfig',
        'Tempest\Core\CouldNotStoreDiscoveryCache' => 'Tempest\Discovery\CouldNotStoreDiscoveryCache',
        'Tempest\Core\DiscoveryCacheInitializer' => 'Tempest\Discovery\DiscoveryCacheInitializer',
        'Tempest\Core\DiscoveryDiscovery' => 'Tempest\Discovery\DiscoveryDiscovery',
    ];

    public function getNodeTypes(): array
    {
        return [
            UseItem::class,
            FullyQualified::class,
        ];
    }

    public function refactor(Node $node): ?Node
    {
        if ($node instanceof UseItem) {
            $name = $node->name->toString();

            if (isset(self::CLASS_RENAMES[$name])) {
                $node->name = new Name(self::CLASS_RENAMES[$name]);

                return $node;
            }

            return null;
        }

        if ($node instanceof FullyQualified) {
            $name = $node->toString();

            if (isset(self::CLASS_RENAMES[$name])) {
                return new FullyQualified(self::CLASS_RENAMES[$name]);
            }
        }

        return null;
    }
}
