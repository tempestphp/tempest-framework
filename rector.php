<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector;
use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\Config\RectorConfig;
use Rector\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector;
use Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector;
use Rector\Php81\Rector\Array_\ArrayToFirstClassCallableRector;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Php82\Rector\Param\AddSensitiveParameterAttributeRector;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\Php85\Rector\FuncCall\ArrayKeyExistsNullToEmptyStringRector;
use Rector\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\SafeDeclareStrictTypesRector;
use Tempest\Rector\ImportClassNamesRector;

return RectorConfig::configure()
    ->withCache('./.cache/rector', FileCacheStorage::class)
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/packages',
    ])
    ->withSkipPath(__DIR__ . '/tests/PHPStan/QueryFunctionDynamicReturnTypeExtension.php')
    ->withConfiguredRule(AddSensitiveParameterAttributeRector::class, [
        'sensitive_parameters' => [
            'password',
            'secret',
        ],
    ])
    ->withSkip([
        '*.stub.php',
        '*.input.php',
        '*.expected.php',
        '*/Fixtures/*',
        __DIR__ . '/packages/intl/bin/plural-rules.php',
        AddOverrideAttributeToOverriddenMethodsRector::class,
        ArrayToFirstClassCallableRector::class,
        NullToStrictStringFuncCallArgRector::class,
        ReadOnlyPropertyRector::class,
        AddSensitiveParameterAttributeRector::class,
        RestoreDefaultNullToNullableTypePropertyRector::class,
        StaticCallOnNonStaticToInstanceCallRector::class,
        EncapsedStringsToSprintfRector::class,
        PrivatizeFinalClassMethodRector::class,
        IssetOnPropertyObjectToPropertyExistsRector::class,
        CatchExceptionNameMatchingTypeRector::class,
        ArrayKeyExistsNullToEmptyStringRector::class,
        StringClassNameToClassConstantRector::class,
        ReturnBinaryOrToEarlyReturnRector::class,
        SafeDeclareStrictTypesRector::class,
    ])
    ->withConfiguredRule(ImportClassNamesRector::class, [
        'importShortClasses' => true,
        'excludedClasses' => [
            '\Redis',
        ],
    ])
    ->withPreparedSets(
        codeQuality: true,
        codingStyle: true,
        privatization: true,
        naming: false,
        earlyReturn: true,
    )
    ->withPhpSets(php85: true);
