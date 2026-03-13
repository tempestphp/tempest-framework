<?php

namespace Tempest\Upgrade\Tempest34;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use Rector\Rector\AbstractRector;

final class UpdateKernelDiscoveryPropertiesRector extends AbstractRector
{
    private const array PROPERTY_RENAMES = [
        'discoveryLocations' => 'locations',
        'discoveryClasses' => 'classes',
    ];

    public function getNodeTypes(): array
    {
        return [
            PropertyFetch::class,
        ];
    }

    public function refactor(Node $node): ?Node
    {
        if (! $node instanceof PropertyFetch) {
            return null;
        }

        if (! $node->name instanceof Identifier) {
            return null;
        }

        $propertyName = $node->name->toString();

        if (! isset(self::PROPERTY_RENAMES[$propertyName])) {
            return null;
        }

        if (! $this->isKernelType($node->var)) {
            return null;
        }

        // Transform $kernel->discoveryLocations to $kernel->discoveryConfig->locations
        return new PropertyFetch(
            new PropertyFetch($node->var, 'discoveryConfig'),
            self::PROPERTY_RENAMES[$propertyName],
        );
    }

    private function isKernelType(Node\Expr $expr): bool
    {
        $type = $this->nodeTypeResolver->getType($expr);

        foreach ($type->getObjectClassNames() as $className) {
            if ($className === 'Tempest\Core\Kernel' || is_subclass_of($className, 'Tempest\Core\Kernel')) {
                return true;
            }
        }

        return false;
    }
}
