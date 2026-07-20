<?php

declare(strict_types=1);

namespace Tests\Tempest\PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassMethodNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @implements Rule<InClassMethodNode>
 */
final class TestMethodNameRule implements Rule
{
    public function getNodeType(): string
    {
        return InClassMethodNode::class;
    }

    /**
     * @param InClassMethodNode $node
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $method = $node->getOriginalNode();

        if (! $method->isPublic() || ! str_starts_with($method->name->toString(), 'test_')) {
            return [];
        }

        if (! $node->getClassReflection()->isSubclassOf(TestCase::class)) {
            return [];
        }

        return [
            RuleErrorBuilder::message('Test methods must use the #[Test] attribute instead of the "test_" prefix.')
                ->identifier('tempest.testMethodName')
                ->line($method->getStartLine())
                ->build(),
        ];
    }
}
