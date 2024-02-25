<?php

declare(strict_types=1);

use Tempest\Http\GenericResponseSender;
use Tempest\Http\ResponseSender;
use Tempest\Http\ResponseSenderInitializer;
use Tests\Tempest\TestCase;

uses(TestCase::class);

test('response sender initializer', function () {
	$initializer = new ResponseSenderInitializer();

	expect($initializer->initialize(ResponseSender::class, $this->container))->toBeInstanceOf(GenericResponseSender::class);
});
