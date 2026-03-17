<?php

namespace Tempest\Upgrade\Tempest3;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\UseItem;
use Rector\Rector\AbstractRector;

final class UpdateExceptionProcessorRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [
            UseItem::class,
            Class_::class,
        ];
    }

    public function refactor(Node $node): ?int
    {
        if ($node instanceof UseItem) {
            $name = $node->name->toString();

            if ($name === 'Tempest\Core\ExceptionProcessor' || $name === 'ExceptionProcessor') {
                $node->name = new Name('Tempest\Core\Exceptions\ExceptionReporter');
            }

            return null;
        }

        if (! $node instanceof Class_) {
            return null;
        }

        $implements = $node->implements;

        $implementsExceptionProcessor = array_find_key(
            array: $implements,
            callback: static fn (Name $name) => $name->toString() === 'Tempest\Core\ExceptionProcessor' || $name->toString() === 'ExceptionProcessor',
        );

        if ($implementsExceptionProcessor === null) {
            return null;
        }

        $implements[$implementsExceptionProcessor] = new Name('\Tempest\Core\Exceptions\ExceptionReporter');
        $node->implements = $implements;

        foreach ($node->stmts as $statement) {
            if (! $statement instanceof ClassMethod) {
                continue;
            }

            if ($statement->name->toString() === 'process') {
                $statement->name = new Identifier('report');
                break;
            }
        }

        return null;
    }
}
