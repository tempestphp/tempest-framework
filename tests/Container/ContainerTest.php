<?php

declare(strict_types=1);

namespace Tests\Tempest\Container;

use DateTime;
use PHPUnit\Framework\TestCase;
use Tempest\Container\GenericContainer;
use Tests\Tempest\Container\Fixtures\BuiltinArrayClass;
use Tests\Tempest\Container\Fixtures\BuiltinTypesWithDefaultsClass;
use Tests\Tempest\Container\Fixtures\CallContainerObjectE;
use Tests\Tempest\Container\Fixtures\ContainerObjectA;
use Tests\Tempest\Container\Fixtures\ContainerObjectB;
use Tests\Tempest\Container\Fixtures\ContainerObjectC;
use Tests\Tempest\Container\Fixtures\ContainerObjectD;
use Tests\Tempest\Container\Fixtures\ContainerObjectE;
use Tests\Tempest\Container\Fixtures\ContainerObjectEInitializer;
use Tests\Tempest\Container\Fixtures\OptionalTypesClass;
use Tests\Tempest\Container\Fixtures\SingletonClass;
use Tests\Tempest\Container\Fixtures\UnionTypesClass;

uses(TestCase::class);

test('get with autowire', function () {
    $container = new GenericContainer();

    $b = $container->get(ContainerObjectB::class);

    expect($b)->toBeInstanceOf(ContainerObjectB::class);
    expect($b->a)->toBeInstanceOf(ContainerObjectA::class);
});

test('get with definition', function () {
    $container = new GenericContainer();

    $container->register(
        ContainerObjectC::class,
        fn () => new ContainerObjectC(prop: 'test'),
    );

    $c = $container->get(ContainerObjectC::class);

    expect($c->prop)->toEqual('test');
});

test('get with initializer', function () {
    $container = new GenericContainer();

    $d = $container->get(ContainerObjectD::class);

    expect($d->prop)->toEqual('test');
});

test('singleton', function () {
    $container = new GenericContainer();

    $container->singleton(SingletonClass::class, fn () => new SingletonClass());

    $instance = $container->get(SingletonClass::class);

    expect($instance::$count)->toEqual(1);

    $instance = $container->get(SingletonClass::class);

    expect($instance::$count)->toEqual(1);
});

test('initialize with can initializer', function () {
    $container = new GenericContainer();

    $container->addInitializer(new ContainerObjectEInitializer());

    $object = $container->get(ContainerObjectE::class);

    expect($object)->toBeInstanceOf(ContainerObjectE::class);
});

test('call tries to transform unmatched values', function () {
    $container = new GenericContainer();
    $container->addInitializer(new ContainerObjectEInitializer());
    $classToCall = new CallContainerObjectE();

    $return = $container->call($classToCall, 'method', input: '1');
    expect($return)->toBeInstanceOf(ContainerObjectE::class);
    expect($return->id)->toBe('default');

    $return = $container->call($classToCall, 'method', input: new ContainerObjectE('other'));
    expect($return)->toBeInstanceOf(ContainerObjectE::class);
    expect($return->id)->toBe('other');
});

test('arrays are automatically created', function () {
    $container = new GenericContainer();

    /**
     * @var BuiltinArrayClass $class
     */
    $class = $container->get(BuiltinArrayClass::class);

    expect($class->anArray)->toBeArray();
    expect($class->anArray)->toBeEmpty();
});

test('builtin defaults are used', function () {
    $container = new GenericContainer();

    /**
     * @var BuiltinTypesWithDefaultsClass $class
     */
    $class = $container->get(BuiltinTypesWithDefaultsClass::class);

    expect($class->aString)->toBe('This is a default value');
});

test('optional types resolve to null', function () {
    $container = new GenericContainer();

    /**
     * @var OptionalTypesClass $class
     */
    $class = $container->get(OptionalTypesClass::class);

    expect($class->aString)->toBeNull();
});

test('union types iterate to resolution', function () {
    $container = new GenericContainer();

    /**
     * @var UnionTypesClass $class
     */
    $class = $container->get(UnionTypesClass::class);

    expect($class->aStringOrDate)->toBeInstanceOf(DateTime::class);
});
