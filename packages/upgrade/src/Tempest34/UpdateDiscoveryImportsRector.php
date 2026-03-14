<?php

namespace Tempest\Upgrade\Tempest34;

use PhpParser\Node;
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
            Node\UseItem::class,
            Node\Name\FullyQualified::class,
        ];
    }

    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Node\UseItem) {
            $name = $node->name->toString();

            if (isset(self::CLASS_RENAMES[$name])) {
                $node->name = new Node\Name(self::CLASS_RENAMES[$name]);

                return $node;
            }

            return null;
        }

        if ($node instanceof Node\Name\FullyQualified) {
            $name = $node->toString();

            if (isset(self::CLASS_RENAMES[$name])) {
                return new Node\Name\FullyQualified(self::CLASS_RENAMES[$name]);
            }
        }

        return null;
    }
}
