<?php

declare(strict_types=1);

namespace Tests\Tempest\PHPStan;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\Type;
use Tempest\Database\Builder\QueryBuilders\QueryBuilder;

final readonly class QueryFunctionDynamicReturnTypeExtension implements DynamicFunctionReturnTypeExtension
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {}

    public function isFunctionSupported(FunctionReflection $functionReflection): bool
    {
        return $functionReflection->getName() === 'Tempest\\Database\\query';
    }

    public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): ?Type
    {
        if (count($functionCall->getArgs()) !== 1) {
            return null;
        }

        $constantStrings = $scope
            ->getType($functionCall->getArgs()[0]->value)
            ->getConstantStrings();

        if (count($constantStrings) !== 1) {
            return null;
        }

        if ($this->reflectionProvider->hasClass($constantStrings[0]->getValue())) {
            return null;
        }

        return new GenericObjectType(QueryBuilder::class, [new ObjectWithoutClassType()]);
    }
}
