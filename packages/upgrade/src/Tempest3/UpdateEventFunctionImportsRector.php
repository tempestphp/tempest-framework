<?php

namespace Tempest\Upgrade\Tempest3;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\UseItem;
use Rector\Rector\AbstractRector;

final class UpdateEventFunctionImportsRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [
            UseItem::class,
            FuncCall::class,
        ];
    }

    public function refactor(Node $node): ?int
    {
        if ($node instanceof UseItem) {
            if ($node->name->toString() === 'Tempest\event') {
                $node->name = new Name('Tempest\EventBus\event');
            }

            if ($node->name->toString() === 'Tempest\listen') {
                $node->name = new Name('Tempest\EventBus\listen');
            }

            return null;
        }

        if ($node instanceof FuncCall && $node->name instanceof Name) {
            $functionName = $node->name->toString();

            if ($functionName === 'Tempest\event') {
                $node->name = new FullyQualified('Tempest\EventBus\event');

                return null;
            }

            if ($functionName === 'Tempest\listen') {
                $node->name = new FullyQualified('Tempest\EventBus\listen');

                return null;
            }
        }

        return null;
    }
}
