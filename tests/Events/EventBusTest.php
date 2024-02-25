<?php

declare(strict_types=1);

use App\Events\ItHappened;
use App\Events\MyEventHandler;
use function Tempest\event;
use Tempest\Events\EventBus;
use Tempest\Events\EventBusConfig;
use Tests\Tempest\Events\Fixtures\MyEventBusMiddleware;
use Tests\Tempest\TestCase;

uses(TestCase::class);

it('works', function () {
    $eventBus = $this->container->get(EventBus::class);

    MyEventHandler::$itHappened = false;

    $eventBus->dispatch(new ItHappened());

    expect(MyEventHandler::$itHappened)->toBeTrue();
});

test('event bus with middleware', function () {
    MyEventBusMiddleware::$hit = false;

    $config = $this->container->get(EventBusConfig::class);

    $config->addMiddleware(new MyEventBusMiddleware());

    event(new ItHappened());

    expect(MyEventBusMiddleware::$hit)->toBeTrue();
});
