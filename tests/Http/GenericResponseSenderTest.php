<?php

declare(strict_types=1);

use Tempest\Http\GenericResponse;
use Tempest\Http\GenericResponseSender;
use Tempest\Http\Status;
use Tests\Tempest\TestCase;

uses(TestCase::class);

test('sending', function () {
    ob_start();

    $response = new GenericResponse(
        status: Status::CREATED,
        body: '{"key": "value"}',
        headers: ['Content-Type' => 'application/json']
    );

    $responseSender = new GenericResponseSender();

    expect($responseSender->send($response))->toBe($response);

    ob_get_clean();
});
